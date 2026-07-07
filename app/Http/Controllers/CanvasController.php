<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CanvasSession;
use App\Models\CanvasSessionDetail;
use App\Models\User;
use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\StokMutasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CanvasController extends Controller
{
    /**
     * Display a listing of canvas sessions.
     */
    public function index(Request $request)
    {
        $query = CanvasSession::with(['sales', 'details'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('kode_sales')) {
            $query->where('kode_sales', $request->kode_sales);
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $canvasSessions = $query->paginate(15)->withQueryString();

        $salesmen = User::where(function ($q) {
            $q->where('role', 'sales')->orWhere('role', 'Salesman')->orWhere('role', 'Admin');
        })->where('status', '1')
          ->where('is_kanvas', 1)
          ->orderBy('name')
          ->get();

        return view('canvas.index', compact('canvasSessions', 'salesmen'));
    }

    /**
     * Show the form for starting a new canvas session.
     */
    public function create()
    {
        $salesmen = User::where(function ($q) {
            $q->where('role', 'sales')->orWhere('role', 'Salesman')->orWhere('role', 'Admin');
        })->where('status', '1')
          ->where('is_kanvas', 1)
          ->orderBy('name')
          ->get();

        return view('canvas.create', compact('salesmen'));
    }

    /**
     * Store a newly created canvas session (loading goods).
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_sales' => 'required|string',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|string|exists:barang,kode_barang',
            'items.*.satuan_id' => 'required|integer|exists:barang_satuan,id',
            'items.*.qty_ambil' => 'required|numeric|min:0.01',
            'items.*.diskon_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $canvasSessionId = DB::transaction(function () use ($request) {
                // Generate atomic no_canvas
                $prefix = 'KVS-' . date('Ymd');
                $lastSession = CanvasSession::where('no_canvas', 'like', $prefix . '-%')
                    ->lockForUpdate()
                    ->orderBy('no_canvas', 'desc')
                    ->first();

                $nextNum = 1;
                if ($lastSession) {
                    $parts = explode('-', $lastSession->no_canvas);
                    $nextNum = (int)end($parts) + 1;
                }
                $noCanvas = $prefix . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

                // Create Canvas Session
                $session = CanvasSession::create([
                    'no_canvas' => $noCanvas,
                    'kode_sales' => $request->kode_sales,
                    'tanggal' => $request->tanggal,
                    'status' => 'pending',
                    'keterangan' => $request->keterangan,
                ]);

                // Create details only
                foreach ($request->items as $item) {
                    CanvasSessionDetail::create([
                        'canvas_session_id' => $session->id,
                        'kode_barang' => $item['kode_barang'],
                        'satuan_id' => $item['satuan_id'],
                        'qty_ambil' => $item['qty_ambil'],
                        'diskon_persen' => isset($item['diskon_persen']) ? floatval($item['diskon_persen']) : 0,
                        'qty_terjual' => 0,
                        'qty_kembali' => 0,
                        'selisih' => $item['qty_ambil'],
                    ]);
                }

                return $session->id;
            });

            return redirect()->route('canvas.show', $canvasSessionId)->with('success', 'Session kanvas berhasil dibuat dan menunggu approval dari Admin.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve the canvas session (Approve loading and deduct stock).
     */
    public function approve($id)
    {
        $canvasSession = CanvasSession::with('details')->findOrFail($id);

        if ($canvasSession->status !== 'pending') {
            return redirect()->route('canvas.show', $id)->with('error', 'DPB ini tidak berada dalam status pending.');
        }

        try {
            DB::transaction(function () use ($canvasSession) {
                $salesman = User::where('nik', $canvasSession->kode_sales)->first();
                $salesName = $salesman ? $salesman->name : $canvasSession->kode_sales;

                // Validate and deduct warehouse stock for each item
                foreach ($canvasSession->details as $detail) {
                    $satuan = BarangSatuan::findOrFail($detail->satuan_id);
                    $qtySmallest = (float)$detail->qty_ambil * ($satuan->isi ?? 1);

                    // Lock and check warehouse stock
                    $barang = Barang::lockForUpdate()->findOrFail($detail->kode_barang);
                    if ($barang->stok < $qtySmallest) {
                        throw new \Exception("Stok barang '{$barang->nama_barang}' tidak mencukupi untuk loading kanvas! Sisa stok gudang: " . $barang->formatStok($barang->stok) . " (Dibutuhkan: " . $barang->formatStok($qtySmallest) . ")");
                    }

                    // Deduct stock and log mutation
                    StokMutasi::log(
                        $detail->kode_barang,
                        $canvasSession->tanggal,
                        'Canvas Ambil',
                        $canvasSession->no_canvas,
                        0,
                        $qtySmallest,
                        Auth::id(),
                        'Loading Canvas (Approve): ' . $salesName
                    );
                }

                // Update session status to loading
                $canvasSession->status = 'loading';
                $canvasSession->approved_at = now();
                $canvasSession->save();
            });

            return redirect()->route('canvas.show', $id)->with('success', 'DPB berhasil disetujui dan stok gudang telah dipotong.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified canvas session.
     */
    public function show($id)
    {
        $canvasSession = CanvasSession::with(['sales', 'details.barang', 'details.barangSatuan'])
            ->findOrFail($id);

        return view('canvas.show', compact('canvasSession'));
    }

    /**
     * Show the form for closing / unloading canvas goods.
     */
    public function edit($id)
    {
        $canvasSession = CanvasSession::with(['sales', 'details.barang', 'details.barangSatuan'])
            ->findOrFail($id);

        if ($canvasSession->status === 'completed') {
            return redirect()->route('canvas.show', $id)->with('error', 'Session kanvas ini sudah selesai.');
        }

        // Auto-correct / sync actual sales before viewing the form
        \App\Services\CanvasService::syncActualSales($canvasSession->kode_sales);
        $canvasSession->load(['details.barang.satuans', 'details.barangSatuan']);

        $activeSessions = collect();
        $accumulatedDetails = collect();

        if ($canvasSession->status === 'loading') {
            // Fetch all loading sessions for this salesman, ordered by tanggal
            $activeSessions = CanvasSession::where('kode_sales', $canvasSession->kode_sales)
                ->where('status', 'loading')
                ->orderBy('tanggal', 'asc')
                ->with(['details.barang.satuans', 'details.barangSatuan'])
                ->get();

            foreach ($activeSessions as $session) {
                foreach ($session->details as $detail) {
                    $key = $detail->kode_barang . '_' . $detail->satuan_id;
                    if (!$accumulatedDetails->has($key)) {
                        $accumulatedDetails->put($key, [
                            'kode_barang' => $detail->kode_barang,
                            'barang' => $detail->barang,
                            'satuan_id' => $detail->satuan_id,
                            'barangSatuan' => $detail->barangSatuan,
                            'qty_ambil' => (float)$detail->qty_ambil,
                            'qty_terjual' => (float)$detail->qty_terjual,
                            'qty_kembali' => (float)$detail->qty_kembali,
                            'detail_ids' => [$detail->id],
                        ]);
                    } else {
                        $item = $accumulatedDetails->get($key);
                        $item['qty_ambil'] += (float)$detail->qty_ambil;
                        $item['qty_terjual'] += (float)$detail->qty_terjual;
                        $item['qty_kembali'] += (float)$detail->qty_kembali;
                        $item['detail_ids'][] = $detail->id;
                        $accumulatedDetails->put($key, $item);
                    }
                }
            }
        }

        return view('canvas.edit', compact('canvasSession', 'activeSessions', 'accumulatedDetails'));
    }

    /**
     * Update the canvas session (Unloading / Evening Return or editing pending quantities).
     */
    public function update(Request $request, $id)
    {
        $canvasSession = CanvasSession::findOrFail($id);

        if ($canvasSession->status === 'completed') {
            return redirect()->route('canvas.show', $id)->with('error', 'Session kanvas ini sudah selesai.');
        }

        $isEditingLoading = $canvasSession->status === 'pending' || ($canvasSession->status === 'loading' && $request->input('mode') === 'edit');

        if ($isEditingLoading) {
            $request->validate([
                'keterangan' => 'nullable|string',
                'details' => 'required|array|min:1',
                'details.*.id' => 'required|integer|exists:canvas_session_details,id',
                'details.*.qty_ambil' => 'required|numeric|min:0.01',
                'details.*.diskon_persen' => 'nullable|numeric|min:0|max:100',
            ]);

            try {
                DB::transaction(function () use ($request, $canvasSession) {
                    $salesman = User::where('nik', $canvasSession->kode_sales)->first();
                    $salesName = $salesman ? $salesman->name : $canvasSession->kode_sales;

                    // 1. Revert stock and delete removed items
                    $submittedIds = collect($request->details)->pluck('id')->toArray();
                    $removedDetails = CanvasSessionDetail::where('canvas_session_id', $canvasSession->id)
                         ->whereNotIn('id', $submittedIds)
                         ->get();

                    foreach ($removedDetails as $detail) {
                        if ($canvasSession->status === 'loading') {
                            // Validate that this removed detail has not been sold or returned yet
                            if ($detail->qty_terjual > 0 || $detail->qty_kembali > 0) {
                                throw new \Exception("Barang '{$detail->barang->nama_barang}' tidak dapat dihapus karena sudah memiliki catatan penjualan atau pengembalian!");
                            }

                            $satuan = BarangSatuan::find($detail->satuan_id);
                            $qtyAmbilSmallest = (float)$detail->qty_ambil * ($satuan->isi ?? 1);

                            if ($qtyAmbilSmallest > 0) {
                                StokMutasi::log(
                                    $detail->kode_barang,
                                    now()->toDateString(),
                                    'Batal Canvas Ambil',
                                    $canvasSession->no_canvas,
                                    $qtyAmbilSmallest,
                                    0,
                                    Auth::id(),
                                    'Batal Loading (Edit Hapus): ' . $salesName
                                );
                            }
                        }
                        $detail->delete();
                    }

                    // 2. Update existing items
                    foreach ($request->details as $item) {
                        $detail = CanvasSessionDetail::where('canvas_session_id', $canvasSession->id)
                            ->findOrFail($item['id']);

                        $newQtyAmbil = (float)$item['qty_ambil'];
                        $newDiskonPersen = isset($item['diskon_persen']) ? (float)$item['diskon_persen'] : 0;

                        // Validation: new loading qty must be >= sold + returned qty
                        if ($newQtyAmbil < ($detail->qty_terjual + $detail->qty_kembali)) {
                            $satuan = BarangSatuan::find($detail->satuan_id);
                            $satuanName = $satuan ? $satuan->satuan : 'PCS';
                            throw new \Exception("Kuantitas loading baru untuk barang '{$detail->barang->nama_barang}' ({$newQtyAmbil} {$satuanName}) tidak boleh lebih kecil dari jumlah yang sudah terjual & dikembalikan ({$detail->qty_terjual} + {$detail->qty_kembali} {$satuanName})!");
                        }

                        if ($canvasSession->status === 'loading') {
                            $satuan = BarangSatuan::findOrFail($detail->satuan_id);
                            $oldQtySmallest = (float)$detail->qty_ambil * ($satuan->isi ?? 1);
                            $newQtySmallest = $newQtyAmbil * ($satuan->isi ?? 1);

                            $diff = $newQtySmallest - $oldQtySmallest;

                            if ($diff > 0) {
                                // Validate and deduct warehouse stock for additional quantity
                                $barang = Barang::lockForUpdate()->findOrFail($detail->kode_barang);
                                if ($barang->stok < $diff) {
                                    throw new \Exception("Stok barang '{$barang->nama_barang}' tidak mencukupi untuk penambahan loading! Sisa stok: " . $barang->formatStok($barang->stok) . " (Dibutuhkan: " . $barang->formatStok($diff) . ")");
                                }

                                StokMutasi::log(
                                    $detail->kode_barang,
                                    $canvasSession->tanggal,
                                    'Canvas Ambil (Edit)',
                                    $canvasSession->no_canvas,
                                    0,
                                    $diff,
                                    Auth::id(),
                                    'Penambahan Loading Canvas (Edit): ' . $salesName
                                );
                            } elseif ($diff < 0) {
                                // Return the decreased quantity back to warehouse
                                StokMutasi::log(
                                    $detail->kode_barang,
                                    now()->toDateString(),
                                    'Batal Canvas Ambil (Edit)',
                                    $canvasSession->no_canvas,
                                    -$diff,
                                    0,
                                    Auth::id(),
                                    'Pengurangan Loading Canvas (Edit): ' . $salesName
                                );
                            }
                        }

                        $detail->qty_ambil = $newQtyAmbil;
                        $detail->diskon_persen = $newDiskonPersen;
                        $detail->selisih = $newQtyAmbil - $detail->qty_terjual - $detail->qty_kembali;
                        $detail->save();
                    }

                    if ($request->filled('keterangan')) {
                        $canvasSession->keterangan = $request->keterangan;
                    } else {
                        $canvasSession->keterangan = null;
                    }
                    $canvasSession->save();
                });

                return redirect()->route('canvas.show', $id)->with('success', 'Detail DPB berhasil diperbarui.');
            } catch (\Exception $e) {
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        }

        $request->validate([
            'keterangan' => 'nullable|string',
            'details' => 'required|array',
            'details.*.id' => 'nullable|integer|exists:canvas_session_details,id',
            'details.*.detail_ids' => 'nullable|string',
            'details.*.qty_kembali' => 'required|numeric|min:0',
        ]);

        // Auto-correct / sync actual sales before processing returns
        \App\Services\CanvasService::syncActualSales($canvasSession->kode_sales);

        try {
            DB::transaction(function () use ($request, $canvasSession) {
                // Find salesman details for stock logging
                $salesman = User::where('nik', $canvasSession->kode_sales)->first();
                $salesName = $salesman ? $salesman->name : $canvasSession->kode_sales;

                // Find all active loading sessions for this salesman
                $activeSessions = CanvasSession::where('kode_sales', $canvasSession->kode_sales)
                    ->where('status', 'loading')
                    ->get();

                foreach ($request->details as $item) {
                    $qtyKembaliTotal = (float)$item['qty_kembali'];

                    // Get detail records to update
                    $detailIds = [];
                    if (isset($item['detail_ids']) && !empty($item['detail_ids'])) {
                        $detailIds = explode(',', $item['detail_ids']);
                    } elseif (isset($item['id'])) {
                        $detailIds = [$item['id']];
                    }

                    $detailsToUpdate = CanvasSessionDetail::whereIn('id', $detailIds)->get();

                    if ($detailsToUpdate->isEmpty()) {
                        continue;
                    }

                    // Distribute returned qty across detail records
                    $remainingReturn = $qtyKembaliTotal;
                    $lastDetail = $detailsToUpdate->last();

                    foreach ($detailsToUpdate as $detail) {
                        $satuan = BarangSatuan::findOrFail($detail->satuan_id);
                        $isi = $satuan->isi ?? 1;

                        $qtyAmbil = (float)$detail->qty_ambil;
                        $qtyTerjual = (float)$detail->qty_terjual;
                        $maxReturnForThisDetail = max(0.0, $qtyAmbil - $qtyTerjual);

                        $assignedReturn = min($remainingReturn, $maxReturnForThisDetail);

                        // Jika ini detail terakhir dan masih ada sisa, force assign semua sisa
                        if ($detail->id === $lastDetail->id && $remainingReturn > 0) {
                            $assignedReturn = $remainingReturn;
                        }

                        $qtyKembaliSmallest = $assignedReturn * $isi;

                        // Replenish warehouse stock by returned quantity and log mutation
                        if ($qtyKembaliSmallest > 0) {
                            StokMutasi::log(
                                $detail->kode_barang,
                                now()->toDateString(),
                                'Canvas Kembali',
                                $detail->session->no_canvas ?? $canvasSession->no_canvas,
                                $qtyKembaliSmallest,
                                0,
                                Auth::id(),
                                'Pengembalian Canvas: ' . $salesName
                            );
                        }

                        // Update detail record
                        $detail->qty_kembali = $assignedReturn;
                        // Calculate discrepancy
                        $detail->selisih = $qtyAmbil - $qtyTerjual - $assignedReturn;
                        $detail->save();

                        $remainingReturn -= $assignedReturn;
                    }
                }

                // Update session headers to completed for all loading sessions of this salesman
                foreach ($activeSessions as $session) {
                    $session->status = 'completed';
                    if ($session->id === $canvasSession->id && $request->filled('keterangan')) {
                        $session->keterangan = $request->keterangan;
                    }
                    $session->save();
                }
            });

            return redirect()->route('canvas.show', $id)->with('success', 'Session kanvas berhasil diselesaikan. Sisa barang telah dikembalikan ke gudang.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove / cancel the specified canvas session.
     */
    public function destroy($id)
    {
        $canvasSession = CanvasSession::with('details')->findOrFail($id);

        try {
            DB::transaction(function () use ($canvasSession) {
                // If it was not completed, we must revert/return all taken stock back to the warehouse
                if ($canvasSession->status === 'loading') {
                    // Find salesman details
                    $salesman = User::where('nik', $canvasSession->kode_sales)->first();
                    $salesName = $salesman ? $salesman->name : $canvasSession->kode_sales;

                    foreach ($canvasSession->details as $detail) {
                        $satuan = BarangSatuan::find($detail->satuan_id);
                        $qtyAmbilSmallest = (float)$detail->qty_ambil * ($satuan->isi ?? 1);

                        if ($qtyAmbilSmallest > 0) {
                            StokMutasi::log(
                                $detail->kode_barang,
                                now()->toDateString(),
                                'Batal Canvas Ambil',
                                $canvasSession->no_canvas,
                                $qtyAmbilSmallest,
                                0,
                                Auth::id(),
                                'Batal Canvas (Hapus): ' . $salesName
                            );
                        }
                    }
                }

                $canvasSession->delete();
            });

            return redirect()->route('canvas.index')->with('success', 'Session kanvas berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Print the canvas session report (Rekap Terjual & Mutasi).
     */
    public function print($id)
    {
        $canvasSession = CanvasSession::with(['sales', 'details.barang', 'details.barangSatuan'])
            ->findOrFail($id);

        // Gunakan approved_at sebagai start (jika ada), fallback ke created_at
        $startDate = $canvasSession->approved_at ?? $canvasSession->created_at;
        $endDate = $canvasSession->status === 'completed' ? $canvasSession->updated_at : now();

        $invoices = \App\Models\Penjualan::where('kode_sales', $canvasSession->kode_sales)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('batal', 0)
            ->with(['pelanggan', 'details.barang', 'details.barangSatuan'])
            ->get();

        return view('canvas.print', compact('canvasSession', 'invoices'));
    }

    /**
     * Display a listing of canvas returns (completed sessions).
     */
    public function returnsIndex(Request $request)
    {
        $query = CanvasSession::with(['sales', 'details'])
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('kode_sales')) {
            $query->where('kode_sales', $request->kode_sales);
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('updated_at', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('updated_at', '<=', $request->tanggal_akhir);
        }

        $canvasSessions = $query->paginate(15)->withQueryString();

        $salesmen = User::where(function ($q) {
            $q->where('role', 'sales')->orWhere('role', 'Salesman')->orWhere('role', 'Admin');
        })->where('status', '1')
          ->where('is_kanvas', 1)
          ->orderBy('name')
          ->get();

        return view('canvas.returns_index', compact('canvasSessions', 'salesmen'));
    }

    /**
     * Show the form for creating a new return.
     */
    public function returnsCreate(Request $request)
    {
        $selectedSales = $request->input('kode_sales');
        if ($selectedSales) {
            // Auto-correct / sync actual sales before viewing the form
            \App\Services\CanvasService::syncActualSales($selectedSales);
        }
        $accumulatedDetails = collect();
        $activeSessions = collect();
        $invoices = collect();

        // Get list of sales who have active loading DPBs
        $salesmen = User::where(function ($q) {
            $q->where('role', 'sales')->orWhere('role', 'Salesman')->orWhere('role', 'Admin');
        })->where('status', '1')
          ->where('is_kanvas', 1)
          ->whereIn('nik', function($q) {
              $q->select('kode_sales')
                ->from('canvas_sessions')
                ->where('status', 'loading');
          })
          ->orderBy('name')
          ->get();

        if ($selectedSales) {
            // Fetch all loading sessions for this salesman, ordered by tanggal
            $activeSessions = CanvasSession::where('kode_sales', $selectedSales)
                ->where('status', 'loading')
                ->orderBy('tanggal', 'asc')
                ->with(['details.barang.satuans', 'details.barangSatuan'])
                ->get();

            foreach ($activeSessions as $session) {
                foreach ($session->details as $detail) {
                    $key = $detail->kode_barang . '_' . $detail->satuan_id;
                    if (!$accumulatedDetails->has($key)) {
                        $accumulatedDetails->put($key, [
                            'kode_barang' => $detail->kode_barang,
                            'barang' => $detail->barang,
                            'satuan_id' => $detail->satuan_id,
                            'barangSatuan' => $detail->barangSatuan,
                            'qty_ambil' => (float)$detail->qty_ambil,
                            'qty_terjual' => 0.0, // Will be recalculated from actual invoices below
                            'qty_kembali' => (float)$detail->qty_kembali,
                            'detail_ids' => [$detail->id],
                        ]);
                    } else {
                        $item = $accumulatedDetails->get($key);
                        $item['qty_ambil'] += (float)$detail->qty_ambil;
                        // qty_terjual stays 0 here, recalculated below
                        $item['qty_kembali'] += (float)$detail->qty_kembali;
                        $item['detail_ids'][] = $detail->id;
                        $accumulatedDetails->put($key, $item);
                    }
                }
            }

            if (!$activeSessions->isEmpty()) {
                // Gunakan approved_at (waktu DPB di-approve/mulai loading) sebagai patokan awal,
                // bukan created_at (waktu DPB dibuat/masih pending).
                // trackSale() hanya berjalan saat ada session berstatus 'loading',
                // sehingga faktur sebelum approval tidak masuk ke qty_terjual (Section I).
                // Dengan approved_at, Section II (faktur) sinkron dengan Section I (qty_terjual).
                $minStartAt = $activeSessions
                    ->map(fn($s) => $s->approved_at ?? $s->created_at)
                    ->min();

                $invoices = \App\Models\Penjualan::where('kode_sales', $selectedSales)
                    ->where('created_at', '>=', $minStartAt)
                    ->where('batal', 0)
                    ->with(['pelanggan', 'details.barang', 'details.barangSatuan'])
                    ->get();

                // --- FIX: Hitung qty_terjual DARI FAKTUR NYATA (Section II) ---
                // qty_terjual di canvas_session_details bisa corrupt karena trackSale
                // dipanggil berkali-kali untuk faktur yang sama (double submit, dll).
                // Solusi: hitung langsung dari penjualan_details yang terpercaya.
                foreach ($invoices as $inv) {
                    foreach ($inv->details as $det) {
                        $detSatuan = $det->barangSatuan;
                        $detIsi = $detSatuan ? (float)$detSatuan->isi : 1.0;
                        $qtySmallest = (float)$det->qty * $detIsi;

                        // Cari key yang cocok di accumulatedDetails berdasarkan kode_barang
                        $matchedKey = null;
                        foreach ($accumulatedDetails as $k => $accItem) {
                            if ($accItem['kode_barang'] === $det->kode_barang) {
                                $matchedKey = $k;
                                break;
                            }
                        }

                        if ($matchedKey !== null) {
                            $accItem = $accumulatedDetails->get($matchedKey);
                            $canvasIsi = $accItem['barangSatuan'] ? (float)$accItem['barangSatuan']->isi : 1.0;
                            // Konversi dari smallest ke satuan canvas
                            $qtyInCanvasUnit = $canvasIsi > 0 ? ($qtySmallest / $canvasIsi) : $qtySmallest;
                            $accItem['qty_terjual'] += $qtyInCanvasUnit;
                            $accumulatedDetails->put($matchedKey, $accItem);
                        }
                    }
                }
            }
        }

        return view('canvas.returns_create', compact('salesmen', 'selectedSales', 'activeSessions', 'accumulatedDetails', 'invoices'));
    }

    /**
     * Store the return transaction and distribute returned quantities.
     */
    public function returnsStore(Request $request)
    {
        $request->validate([
            'kode_sales' => 'required|string|exists:users,nik',
            'keterangan' => 'nullable|string',
            'details' => 'required|array',
            'details.*.detail_ids' => 'required|string',
            'details.*.qty_kembali' => 'required|numeric|min:0',
        ]);

        $selectedSales = $request->kode_sales;

        // Auto-correct / sync actual sales before processing return
        \App\Services\CanvasService::syncActualSales($selectedSales);

        try {
            DB::transaction(function () use ($request, $selectedSales) {
                $salesman = User::where('nik', $selectedSales)->first();
                $salesName = $salesman ? $salesman->name : $selectedSales;

                // Find all active loading sessions for this salesman
                $activeSessions = CanvasSession::where('kode_sales', $selectedSales)
                    ->where('status', 'loading')
                    ->get();

                if ($activeSessions->isEmpty()) {
                    throw new \Exception("Tidak ada sesi DPB aktif untuk sales ini.");
                }

                foreach ($request->details as $item) {
                    $qtyKembaliTotal = (float)$item['qty_kembali'];
                    $detailIds = explode(',', $item['detail_ids']);

                    $detailsToUpdate = CanvasSessionDetail::whereIn('id', $detailIds)->get();

                    if ($detailsToUpdate->isEmpty()) {
                        continue;
                    }

                    // Distribute returned qty across detail records
                    $remainingReturn = $qtyKembaliTotal;
                    $lastDetail = $detailsToUpdate->last();

                    foreach ($detailsToUpdate as $detail) {
                        $satuan = BarangSatuan::findOrFail($detail->satuan_id);
                        $isi = $satuan->isi ?? 1;

                        $qtyAmbil = (float)$detail->qty_ambil;
                        $qtyTerjual = (float)$detail->qty_terjual;
                        $maxReturnForThisDetail = max(0.0, $qtyAmbil - $qtyTerjual);

                        $assignedReturn = min($remainingReturn, $maxReturnForThisDetail);

                        // Jika ini detail terakhir dan masih ada sisa, force assign semua sisa
                        if ($detail->id === $lastDetail->id && $remainingReturn > 0) {
                            $assignedReturn = $remainingReturn;
                        }

                        $qtyKembaliSmallest = $assignedReturn * $isi;

                        // Replenish warehouse stock by returned quantity and log mutation
                        if ($qtyKembaliSmallest > 0) {
                            StokMutasi::log(
                                $detail->kode_barang,
                                now()->toDateString(),
                                'Canvas Kembali',
                                $detail->session->no_canvas ?? $activeSessions->first()->no_canvas,
                                $qtyKembaliSmallest,
                                0,
                                Auth::id(),
                                'Pengembalian Canvas: ' . $salesName
                            );
                        }

                        // Update detail record
                        $detail->qty_kembali = $assignedReturn;
                        $detail->selisih = $qtyAmbil - $qtyTerjual - $assignedReturn;
                        $detail->save();

                        $remainingReturn -= $assignedReturn;
                    }
                }

                // Update session headers to completed for all loading sessions of this salesman
                foreach ($activeSessions as $session) {
                    $session->status = 'completed';
                    if ($request->filled('keterangan')) {
                        $session->keterangan = $request->keterangan;
                    }
                    $session->save();
                }
            });

            return redirect()->route('canvas.returns.index')->with('success', 'Pengembalian barang canvas berhasil diproses dan sisa barang dikembalikan ke gudang.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Generate canvas report (Dari tanggal s/d tanggal).
     */
    public function report(Request $request)
    {
        $tanggalMulai = $request->input('tanggal_mulai', now()->startOfMonth()->toDateString());
        $tanggalAkhir = $request->input('tanggal_akhir', now()->toDateString());
        $kodeSales = $request->input('kode_sales');

        $query = CanvasSessionDetail::query()
            ->join('canvas_sessions', 'canvas_session_details.canvas_session_id', '=', 'canvas_sessions.id')
            ->whereBetween('canvas_sessions.tanggal', [$tanggalMulai, $tanggalAkhir]);

        if ($kodeSales) {
            $query->where('canvas_sessions.kode_sales', $kodeSales);
        }

        $reportData = $query->select(
                'canvas_session_details.kode_barang',
                'canvas_session_details.satuan_id',
                DB::raw('SUM(canvas_session_details.qty_ambil) as total_ambil'),
                DB::raw('SUM(canvas_session_details.qty_terjual) as total_terjual'),
                DB::raw('SUM(canvas_session_details.qty_kembali) as total_kembali'),
                DB::raw('SUM(canvas_session_details.selisih) as total_selisih')
            )
            ->groupBy('canvas_session_details.kode_barang', 'canvas_session_details.satuan_id')
            ->with(['barang', 'barangSatuan'])
            ->get();

        $salesmen = User::where(function ($q) {
            $q->where('role', 'sales')->orWhere('role', 'Salesman')->orWhere('role', 'Admin');
        })->where('status', '1')
          ->where('is_kanvas', 1)
          ->orderBy('name')
          ->get();

        return view('canvas.report', compact('reportData', 'salesmen', 'tanggalMulai', 'tanggalAkhir', 'kodeSales'));
    }

    /**
     * Cancel/delete a completed canvas session return.
     * Reverts status to loading and deducts returned stock from warehouse.
     */
    public function returnsDestroy($id)
    {
        $canvasSession = CanvasSession::with('details')->findOrFail($id);

        if ($canvasSession->status !== 'completed') {
            return redirect()->back()->with('error', 'Sesi kanvas ini belum selesai.');
        }

        try {
            DB::transaction(function () use ($canvasSession) {
                $salesman = User::where('nik', $canvasSession->kode_sales)->first();
                $salesName = $salesman ? $salesman->name : $canvasSession->kode_sales;

                foreach ($canvasSession->details as $detail) {
                    $satuan = BarangSatuan::findOrFail($detail->satuan_id);
                    $isi = $satuan->isi ?? 1;
                    $qtyKembaliSmallest = (float)$detail->qty_kembali * $isi;

                    if ($qtyKembaliSmallest > 0) {
                        // Validate warehouse stock
                        $barang = Barang::lockForUpdate()->findOrFail($detail->kode_barang);
                        if ($barang->stok < $qtyKembaliSmallest) {
                            throw new \Exception("Stok gudang tidak mencukupi untuk membatalkan pengembalian barang '{$barang->nama_barang}'! Sisa stok gudang: " . $barang->formatStok($barang->stok) . " (Dibutuhkan: " . $barang->formatStok($qtyKembaliSmallest) . ")");
                        }

                        // Revert: deduct returned stock from warehouse
                        StokMutasi::log(
                            $detail->kode_barang,
                            now()->toDateString(),
                            'Batal Canvas Kembali',
                            $canvasSession->no_canvas,
                            0,
                            $qtyKembaliSmallest,
                            Auth::id(),
                            'Batal Pengembalian Canvas: ' . $salesName
                        );
                    }

                    // Reset details
                    $detail->qty_kembali = 0;
                    $detail->selisih = (float)$detail->qty_ambil - (float)$detail->qty_terjual;
                    $detail->save();
                }

                // Revert status to loading
                $canvasSession->status = 'loading';
                $canvasSession->save();
            });

            return redirect()->route('canvas.returns.index')->with('success', 'Setoran penjualan kanvas berhasil dibatalkan dan status kembali menjadi Loading.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing a completed canvas session return.
     */
    public function returnsEdit($id)
    {
        $canvasSession = CanvasSession::with(['sales', 'details.barang.satuans', 'details.barangSatuan'])
            ->findOrFail($id);

        if ($canvasSession->status !== 'completed') {
            return redirect()->route('canvas.returns.index')->with('error', 'Sesi kanvas ini belum selesai.');
        }

        return view('canvas.returns_edit', compact('canvasSession'));
    }

    /**
     * Update the completed canvas session return.
     */
    public function returnsUpdate(Request $request, $id)
    {
        $canvasSession = CanvasSession::findOrFail($id);

        if ($canvasSession->status !== 'completed') {
            return redirect()->route('canvas.returns.index')->with('error', 'Sesi kanvas ini belum selesai.');
        }

        $request->validate([
            'keterangan' => 'nullable|string',
            'details' => 'required|array',
            'details.*.id' => 'required|integer|exists:canvas_session_details,id',
            'details.*.qty_kembali' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $canvasSession) {
                $salesman = User::where('nik', $canvasSession->kode_sales)->first();
                $salesName = $salesman ? $salesman->name : $canvasSession->kode_sales;

                foreach ($request->details as $item) {
                    $detail = CanvasSessionDetail::where('canvas_session_id', $canvasSession->id)
                        ->findOrFail($item['id']);

                    $newQtyKembali = (float)$item['qty_kembali'];
                    $oldQtyKembali = (float)$detail->qty_kembali;

                    // Validation: cannot return more than (ambil - terjual)
                    $maxReturn = (float)$detail->qty_ambil - (float)$detail->qty_terjual;
                    if ($newQtyKembali > $maxReturn) {
                        $satuan = BarangSatuan::find($detail->satuan_id);
                        $satuanName = $satuan ? $satuan->satuan : 'PCS';
                        throw new \Exception("Kuantitas pengembalian untuk barang '{$detail->barang->nama_barang}' ({$newQtyKembali} {$satuanName}) tidak boleh melebihi sisa barang ({$maxReturn} {$satuanName})!");
                    }

                    if ($newQtyKembali != $oldQtyKembali) {
                        $satuan = BarangSatuan::findOrFail($detail->satuan_id);
                        $oldQtySmallest = $oldQtyKembali * ($satuan->isi ?? 1);
                        $newQtySmallest = $newQtyKembali * ($satuan->isi ?? 1);

                        $diff = $newQtySmallest - $oldQtySmallest;

                        if ($diff > 0) {
                            // We are returning MORE goods to warehouse (warehouse stock increases)
                            StokMutasi::log(
                                $detail->kode_barang,
                                now()->toDateString(),
                                'Canvas Kembali (Edit)',
                                $canvasSession->no_canvas,
                                $diff,
                                0,
                                Auth::id(),
                                'Koreksi Pengembalian Canvas (Tambah): ' . $salesName
                            );
                        } elseif ($diff < 0) {
                            // We are returning LESS goods (warehouse stock decreases)
                            // Lock and validate warehouse stock
                            $barang = Barang::lockForUpdate()->findOrFail($detail->kode_barang);
                            $decrease = -$diff;
                            if ($barang->stok < $decrease) {
                                throw new \Exception("Stok gudang tidak mencukupi untuk membatalkan sebagian pengembalian barang '{$barang->nama_barang}'! Sisa stok gudang: " . $barang->formatStok($barang->stok) . " (Dibutuhkan: " . $barang->formatStok($decrease) . ")");
                            }

                            StokMutasi::log(
                                $detail->kode_barang,
                                now()->toDateString(),
                                'Batal Canvas Kembali (Edit)',
                                $canvasSession->no_canvas,
                                0,
                                $decrease,
                                Auth::id(),
                                'Koreksi Pengembalian Canvas (Kurang): ' . $salesName
                            );
                        }
                    }

                    $detail->qty_kembali = $newQtyKembali;
                    $detail->selisih = (float)$detail->qty_ambil - (float)$detail->qty_terjual - $newQtyKembali;
                    $detail->save();
                }

                if ($request->filled('keterangan')) {
                    $canvasSession->keterangan = $request->keterangan;
                } else {
                    $canvasSession->keterangan = null;
                }
                $canvasSession->save();
            });

            return redirect()->route('canvas.returns.index')->with('success', 'Setoran penjualan kanvas berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
