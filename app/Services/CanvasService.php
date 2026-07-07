<?php

namespace App\Services;

use App\Models\CanvasSession;
use App\Models\CanvasSessionDetail;
use App\Models\BarangSatuan;
use App\Models\User;

class CanvasService
{
    /**
     * Check if a salesman (by NIK) is configured for canvasing.
     */
    public static function isCanvasSalesman(?string $nik): bool
    {
        if (empty($nik)) {
            return false;
        }

        $user = User::where('nik', $nik)->first();
        return $user ? (bool)$user->is_kanvas : false;
    }

    /**
     * Get all active sessions for a salesman.
     */
    public static function getActiveSessions(string $kodeSales): \Illuminate\Support\Collection
    {
        return CanvasSession::where('kode_sales', $kodeSales)
            ->where('status', 'loading')
            ->orderBy('tanggal', 'asc')
            ->get();
    }

    /**
     * Get the active canvas session for a salesman.
     */
    public static function getActiveSession(string $kodeSales, $tanggal = null): ?CanvasSession
    {
        $query = CanvasSession::where('kode_sales', $kodeSales)
            ->where('status', 'loading');

        if ($tanggal) {
            // Support searching session around a specific date
            $query->where('tanggal', $tanggal);
        }

        // Fallback to most recent active session if not found on specific date
        $session = $query->orderBy('tanggal', 'desc')->first();
        if (!$session && $tanggal) {
            $session = CanvasSession::where('kode_sales', $kodeSales)
                ->where('status', 'loading')
                ->orderBy('tanggal', 'desc')
                ->first();
        }

        return $session;
    }

    /**
     * Convert quantities between different units.
     */
    public static function convertQuantity(float $qty, ?int $fromSatuanId, ?int $toSatuanId, string $kodeBarang): float
    {
        if ($fromSatuanId === $toSatuanId) {
            return $qty;
        }

        // Get conversion multiplier (isi) for source unit
        $fromIsi = 1.0;
        if ($fromSatuanId) {
            $fromSatuan = BarangSatuan::find($fromSatuanId);
            $fromIsi = $fromSatuan ? (float)$fromSatuan->isi : 1.0;
        }

        // Get conversion multiplier (isi) for target unit
        $toIsi = 1.0;
        if ($toSatuanId) {
            $toSatuan = BarangSatuan::find($toSatuanId);
            $toIsi = $toSatuan ? (float)$toSatuan->isi : 1.0;
        }

        // Quantity in smallest unit (e.g. PCS) = qty * fromIsi
        // Quantity in target unit = quantity in smallest unit / toIsi
        return $qty * ($fromIsi / $toIsi);
    }

    /**
     * Get the accumulated remaining stock (in smallest unit) for a specific item across all active sessions.
     */
    public static function getAccumulatedStock(string $kodeSales, string $kodeBarang): float
    {
        $sessions = self::getActiveSessions($kodeSales);
        $totalStock = 0.0;

        foreach ($sessions as $session) {
            $detail = $session->details()->where('kode_barang', $kodeBarang)->first();
            if ($detail) {
                $satuan = BarangSatuan::find($detail->satuan_id);
                $isi = $satuan ? (float)$satuan->isi : 1.0;

                $qtyAmbilSmallest = (float)$detail->qty_ambil * $isi;
                $qtyTerjualSmallest = (float)$detail->qty_terjual * $isi;
                $qtyKembaliSmallest = (float)$detail->qty_kembali * $isi;

                $remaining = max(0.0, $qtyAmbilSmallest - $qtyTerjualSmallest - $qtyKembaliSmallest);
                $totalStock += $remaining;
            }
        }

        return $totalStock;
    }

    /**
     * Check if a specific item is loaded in any active sessions.
     */
    public static function hasItemInActiveSessions(string $kodeSales, string $kodeBarang): bool
    {
        $sessions = self::getActiveSessions($kodeSales);
        foreach ($sessions as $session) {
            $exists = $session->details()->where('kode_barang', $kodeBarang)->exists();
            if ($exists) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the accumulated active details for a salesman.
     */
    public static function getAccumulatedActiveDetails(string $kodeSales): \Illuminate\Support\Collection
    {
        $sessions = self::getActiveSessions($kodeSales);
        $accumulated = collect();

        foreach ($sessions as $session) {
            foreach ($session->details as $detail) {
                $satuan = BarangSatuan::find($detail->satuan_id);
                $isi = $satuan ? (float)$satuan->isi : 1.0;

                $qtyAmbilSmallest = (float)$detail->qty_ambil * $isi;
                $qtyTerjualSmallest = (float)$detail->qty_terjual * $isi;
                $qtyKembaliSmallest = (float)$detail->qty_kembali * $isi;
                $remainingSmallest = max(0.0, $qtyAmbilSmallest - $qtyTerjualSmallest - $qtyKembaliSmallest);

                if (!$accumulated->has($detail->kode_barang)) {
                    $accumulated->put($detail->kode_barang, [
                        'kode_barang' => $detail->kode_barang,
                        'qty_ambil_smallest' => $qtyAmbilSmallest,
                        'qty_terjual_smallest' => $qtyTerjualSmallest,
                        'qty_kembali_smallest' => $qtyKembaliSmallest,
                        'remaining_smallest' => $remainingSmallest,
                        'diskon_persen' => (float)$detail->diskon_persen,
                        'satuan_id' => $detail->satuan_id,
                    ]);
                } else {
                    $exist = $accumulated->get($detail->kode_barang);
                    $exist['qty_ambil_smallest'] += $qtyAmbilSmallest;
                    $exist['qty_terjual_smallest'] += $qtyTerjualSmallest;
                    $exist['qty_kembali_smallest'] += $qtyKembaliSmallest;
                    $exist['remaining_smallest'] += $remainingSmallest;
                    $exist['diskon_persen'] = max($exist['diskon_persen'], (float)$detail->diskon_persen);
                    $accumulated->put($detail->kode_barang, $exist);
                }
            }
        }

        return $accumulated;
    }

    /**
     * Track a sale and increment the sold quantity in the active canvas sessions.
     */
    public static function trackSale($penjualan): void
    {
        \Log::info("trackSale called for: " . $penjualan->no_faktur);
        if (!self::isCanvasSalesman($penjualan->kode_sales)) {
            \Log::info("isCanvasSalesman failed for: " . ($penjualan->kode_sales ?? 'null'));
            return;
        }

        $activeSessions = self::getActiveSessions($penjualan->kode_sales);
        if ($activeSessions->isEmpty()) {
            \Log::info("No active loading sessions found for: " . $penjualan->kode_sales);
            return;
        }

        if (!$penjualan->relationLoaded('details')) {
            $penjualan->load('details');
        }

        \Log::info("details count in trackSale: " . $penjualan->details->count());

        foreach ($penjualan->details as $detail) {
            $qtySmallest = self::convertQuantity(
                (float)$detail->qty,
                $detail->satuan_id,
                null,
                $detail->kode_barang
            );

            $lastDetailToUpdate = null;
            $lastIsi = 1.0;

            foreach ($activeSessions as $session) {
                if ($qtySmallest <= 0.0001) {
                    break;
                }

                $canvasDetail = $session->details()
                    ->where('kode_barang', $detail->kode_barang)
                    ->first();

                if ($canvasDetail) {
                    $canvasSatuan = BarangSatuan::find($canvasDetail->satuan_id);
                    $canvasIsi = $canvasSatuan ? (float)$canvasSatuan->isi : 1.0;

                    $qtyAmbilSmallest = (float)$canvasDetail->qty_ambil * $canvasIsi;
                    $qtyTerjualSmallest = (float)$canvasDetail->qty_terjual * $canvasIsi;
                    $qtyKembaliSmallest = (float)$canvasDetail->qty_kembali * $canvasIsi;
                    $availableSmallest = max(0.0, $qtyAmbilSmallest - $qtyTerjualSmallest - $qtyKembaliSmallest);

                    $lastDetailToUpdate = $canvasDetail;
                    $lastIsi = $canvasIsi;

                    if ($availableSmallest > 0) {
                        $toDeductSmallest = min($qtySmallest, $availableSmallest);
                        $toDeductCanvas = $toDeductSmallest / $canvasIsi;

                        $canvasDetail->qty_terjual = (float)$canvasDetail->qty_terjual + $toDeductCanvas;
                        $canvasDetail->selisih = (float)$canvasDetail->qty_ambil - $canvasDetail->qty_terjual - (float)$canvasDetail->qty_kembali;
                        $canvasDetail->save();

                        \Log::info("trackSale: Deducted " . $toDeductCanvas . " units (in canvas unit) from session " . $session->no_canvas);
                        $qtySmallest -= $toDeductSmallest;
                    }
                }
            }

            // If there's still quantity remaining after exhausting all active sessions,
            // do NOT force-inject beyond what was loaded (qty_ambil).
            // Log a warning instead so it can be investigated.
            if ($qtySmallest > 0.0001) {
                \Log::warning("trackSale: " . $qtySmallest . " units (smallest) of kode_barang=" . $detail->kode_barang . " could not be matched to any active canvas session for sales " . $penjualan->kode_sales . ". No_faktur: " . $penjualan->no_faktur);
            }
        }
    }

    /**
     * Untrack a sale and decrement the sold quantity in the active canvas sessions.
     */
    public static function untrackSale($penjualan): void
    {
        if (!self::isCanvasSalesman($penjualan->kode_sales)) {
            return;
        }

        $activeSessions = CanvasSession::where('kode_sales', $penjualan->kode_sales)
            ->where('status', 'loading')
            ->orderBy('tanggal', 'desc')
            ->get();

        if ($activeSessions->isEmpty()) {
            return;
        }

        if (!$penjualan->relationLoaded('details')) {
            $penjualan->load('details');
        }

        foreach ($penjualan->details as $detail) {
            $qtySmallest = self::convertQuantity(
                (float)$detail->qty,
                $detail->satuan_id,
                null,
                $detail->kode_barang
            );

            foreach ($activeSessions as $session) {
                if ($qtySmallest <= 0.0001) {
                    break;
                }

                $canvasDetail = $session->details()
                    ->where('kode_barang', $detail->kode_barang)
                    ->first();

                if ($canvasDetail) {
                    $canvasSatuan = BarangSatuan::find($canvasDetail->satuan_id);
                    $canvasIsi = $canvasSatuan ? (float)$canvasSatuan->isi : 1.0;

                    $qtyTerjualSmallest = (float)$canvasDetail->qty_terjual * $canvasIsi;

                    if ($qtyTerjualSmallest > 0) {
                        $toRestoreSmallest = min($qtySmallest, $qtyTerjualSmallest);
                        $toRestoreCanvas = $toRestoreSmallest / $canvasIsi;

                        $canvasDetail->qty_terjual = max(0.0, (float)$canvasDetail->qty_terjual - $toRestoreCanvas);
                        $canvasDetail->selisih = (float)$canvasDetail->qty_ambil - $canvasDetail->qty_terjual - (float)$canvasDetail->qty_kembali;
                        $canvasDetail->save();

                        $qtySmallest -= $toRestoreSmallest;
                    }
                }
            }
        }
    }

    /**
     * Automatically recalculate and sync qty_terjual for active loading sessions of a sales rep
     * based on their actual non-cancelled invoices since the loading session started.
     * This fixes any corrupted/double-counted entries dynamically.
     */
    public static function syncActualSales(string $kodeSales): void
    {
        $activeSessions = CanvasSession::where('kode_sales', $kodeSales)
            ->where('status', 'loading')
            ->orderBy('tanggal', 'asc')
            ->get();

        if ($activeSessions->isEmpty()) {
            return;
        }

        $minStartAt = $activeSessions
            ->map(fn($s) => $s->approved_at ?? $s->created_at)
            ->min();

        $invoices = \App\Models\Penjualan::where('kode_sales', $kodeSales)
            ->where('created_at', '>=', $minStartAt)
            ->where('batal', 0)
            ->with(['details.barangSatuan'])
            ->get();

        $salesSmallest = [];
        foreach ($invoices as $inv) {
            foreach ($inv->details as $det) {
                $detSatuan = $det->barangSatuan;
                $detIsi = $detSatuan ? (float)$detSatuan->isi : 1.0;
                $qtySmallest = (float)$det->qty * $detIsi;
                $salesSmallest[$det->kode_barang] = ($salesSmallest[$det->kode_barang] ?? 0.0) + $qtySmallest;
            }
        }

        foreach ($activeSessions as $session) {
            foreach ($session->details as $detail) {
                $canvasSatuan = BarangSatuan::find($detail->satuan_id);
                $canvasIsi = $canvasSatuan ? (float)$canvasSatuan->isi : 1.0;
                
                $qtyAmbilSmallest = (float)$detail->qty_ambil * $canvasIsi;
                $qtyLeft = $salesSmallest[$detail->kode_barang] ?? 0.0;
                
                $toDeductSmallest = min($qtyLeft, $qtyAmbilSmallest);
                $actualQtyTerjual = $toDeductSmallest / $canvasIsi;
                
                if (isset($salesSmallest[$detail->kode_barang])) {
                    $salesSmallest[$detail->kode_barang] -= $toDeductSmallest;
                }
                
                $detail->qty_terjual = $actualQtyTerjual;
                $detail->selisih = (float)$detail->qty_ambil - $detail->qty_terjual - (float)$detail->qty_kembali;
                $detail->save();
            }
        }
    }
}
