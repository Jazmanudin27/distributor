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
     * Track a sale and increment the sold quantity in the active canvas session.
     */
    public static function trackSale($penjualan): void
    {
        \Log::info("trackSale called for: " . $penjualan->no_faktur);
        if (!self::isCanvasSalesman($penjualan->kode_sales)) {
            \Log::info("isCanvasSalesman failed for: " . ($penjualan->kode_sales ?? 'null'));
            return;
        }

        $session = self::getActiveSession($penjualan->kode_sales, $penjualan->tanggal ? $penjualan->tanggal->toDateString() : null);
        if (!$session) {
            \Log::info("getActiveSession failed for: " . $penjualan->kode_sales . " date: " . ($penjualan->tanggal ? $penjualan->tanggal->toDateString() : 'null'));
            return;
        }

        if (!$penjualan->relationLoaded('details')) {
            $penjualan->load('details');
        }

        \Log::info("details count in trackSale: " . $penjualan->details->count());

        foreach ($penjualan->details as $detail) {
            $canvasDetail = $session->details()
                ->where('kode_barang', $detail->kode_barang)
                ->first();

            if ($canvasDetail) {
                $convertedQty = self::convertQuantity(
                    (float)$detail->qty,
                    $detail->satuan_id,
                    $canvasDetail->satuan_id,
                    $detail->kode_barang
                );

                \Log::info("tracking qty: " . $convertedQty . " for " . $detail->kode_barang);

                $canvasDetail->qty_terjual = (float)$canvasDetail->qty_terjual + $convertedQty;
                $canvasDetail->selisih = (float)$canvasDetail->qty_ambil - $canvasDetail->qty_terjual - (float)$canvasDetail->qty_kembali;
                $canvasDetail->save();
            } else {
                \Log::info("canvasDetail not found for: " . $detail->kode_barang);
            }
        }
    }

    /**
     * Untrack a sale and decrement the sold quantity in the active canvas session.
     */
    public static function untrackSale($penjualan): void
    {
        if (!self::isCanvasSalesman($penjualan->kode_sales)) {
            return;
        }

        $session = self::getActiveSession($penjualan->kode_sales, $penjualan->tanggal ? $penjualan->tanggal->toDateString() : null);
        if (!$session) {
            return;
        }

        if (!$penjualan->relationLoaded('details')) {
            $penjualan->load('details');
        }

        foreach ($penjualan->details as $detail) {
            $canvasDetail = $session->details()
                ->where('kode_barang', $detail->kode_barang)
                ->first();

            if ($canvasDetail) {
                $convertedQty = self::convertQuantity(
                    (float)$detail->qty,
                    $detail->satuan_id,
                    $canvasDetail->satuan_id,
                    $detail->kode_barang
                );

                $canvasDetail->qty_terjual = max(0, (float)$canvasDetail->qty_terjual - $convertedQty);
                $canvasDetail->selisih = (float)$canvasDetail->qty_ambil - $canvasDetail->qty_terjual - (float)$canvasDetail->qty_kembali;
                $canvasDetail->save();
            }
        }
    }
}
