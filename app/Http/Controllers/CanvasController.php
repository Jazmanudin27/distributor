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
                $canvasSession->save();

                // Sync any already recorded sales for this day and salesman
                $invoices = \App\Models\Penjualan::where('kode_sales', $canvasSession->kode_sales)
                    ->where('tanggal', $canvasSession->tanggal)
                    ->where('batal', 0)
                    ->with('details')
                    ->get();

                foreach ($invoices as $invoice) {
                    \App\Services\CanvasService::trackSale($invoice);
                }
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

        if ($canvasSession->status === 'pending') {
            $request->validate([
                'keterangan' => 'nullable|string',
                'details' => 'required|array|min:1',
                'details.*.id' => 'required|integer|exists:canvas_session_details,id',
                'details.*.qty_ambil' => 'required|numeric|min:0.01',
                'details.*.diskon_persen' => 'nullable|numeric|min:0|max:100',
            ]);

            try {
                DB::transaction(function () use ($request, $canvasSession) {
                    $submittedIds = collect($request->details)->pluck('id')->toArray();
                    CanvasSessionDetail::where('canvas_session_id', $canvasSession->id)
                        ->whereNotIn('id', $submittedIds)
                        ->delete();

                    foreach ($request->details as $item) {
                        $detail = CanvasSessionDetail::findOrFail($item['id']);
                        $detail->qty_ambil = (float)$item['qty_ambil'];
                        $detail->diskon_persen = isset($item['diskon_persen']) ? (float)$item['diskon_persen'] : 0;
                        $detail->selisih = (float)$item['qty_ambil'];
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

                    foreach ($detailsToUpdate as $index => $detail) {
                        $satuan = BarangSatuan::findOrFail($detail->satuan_id);
                        $isi = $satuan->isi ?? 1;

                        $qtyAmbil = (float)$detail->qty_ambil;
                        $qtyTerjual = (float)$detail->qty_terjual;
                        $maxReturnForThisDetail = max(0.0, $qtyAmbil - $qtyTerjual);

                        $assignedReturn = min($remainingReturn, $maxReturnForThisDetail);
                        
                        // If it's the last detail in loop and there's still leftover return, force it all here
                        if ($index === $detailsToUpdate->count() - 1 && $remainingReturn > 0) {
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

        $invoices = \App\Models\Penjualan::where('kode_sales', $canvasSession->kode_sales)
            ->where('tanggal', $canvasSession->tanggal)
            ->where('batal', 0)
            ->with(['pelanggan', 'details.barang', 'details.barangSatuan'])
            ->get();

        return view('canvas.print', compact('canvasSession', 'invoices'));
    }
}
