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

                // Find salesman details for stock logging
                $salesman = User::where('nik', $request->kode_sales)->first();
                $salesName = $salesman ? $salesman->name : $request->kode_sales;

                // Create Canvas Session
                $session = CanvasSession::create([
                    'no_canvas' => $noCanvas,
                    'kode_sales' => $request->kode_sales,
                    'tanggal' => $request->tanggal,
                    'status' => 'loading',
                    'keterangan' => $request->keterangan,
                ]);

                // Create details and adjust warehouse stock
                foreach ($request->items as $item) {
                    $satuan = BarangSatuan::findOrFail($item['satuan_id']);
                    $qtySmallest = (float)$item['qty_ambil'] * ($satuan->isi ?? 1);

                    // Lock and check warehouse stock
                    $barang = Barang::lockForUpdate()->findOrFail($item['kode_barang']);
                    if ($barang->stok < $qtySmallest) {
                        throw new \Exception("Stok barang '{$barang->nama_barang}' tidak mencukupi untuk loading kanvas! Sisa stok gudang: " . $barang->formatStok($barang->stok) . " (Dibutuhkan: " . $barang->formatStok($qtySmallest) . ")");
                    }

                    // Deduct stock and log mutation
                    StokMutasi::log(
                        $item['kode_barang'],
                        $request->tanggal,
                        'Canvas Ambil',
                        $noCanvas,
                        0,
                        $qtySmallest,
                        Auth::id(),
                        'Loading Canvas: ' . $salesName
                    );

                    CanvasSessionDetail::create([
                        'canvas_session_id' => $session->id,
                        'kode_barang' => $item['kode_barang'],
                        'satuan_id' => $item['satuan_id'],
                        'qty_ambil' => $item['qty_ambil'],
                        'qty_terjual' => 0,
                        'qty_kembali' => 0,
                        'selisih' => $item['qty_ambil'], // initially the entire taken qty is "discrepancy" (not sold or returned yet)
                    ]);
                }

                // Sync any already recorded sales for this day and salesman
                // (e.g. if they did a sale earlier and then we started a canvas session, or retroactively created it)
                $invoices = \App\Models\Penjualan::where('kode_sales', $request->kode_sales)
                    ->where('tanggal', $request->tanggal)
                    ->where('batal', 0)
                    ->with('details')
                    ->get();

                foreach ($invoices as $invoice) {
                    \App\Services\CanvasService::trackSale($invoice);
                }

                return $session->id;
            });

            return redirect()->route('canvas.show', $canvasSessionId)->with('success', 'Session kanvas berhasil dibuat dan barang telah diambil dari gudang.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
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

        return view('canvas.edit', compact('canvasSession'));
    }

    /**
     * Update the canvas session (Unloading / Evening Return).
     */
    public function update(Request $request, $id)
    {
        $canvasSession = CanvasSession::findOrFail($id);

        if ($canvasSession->status === 'completed') {
            return redirect()->route('canvas.show', $id)->with('error', 'Session kanvas ini sudah selesai.');
        }

        $request->validate([
            'keterangan' => 'nullable|string',
            'details' => 'required|array',
            'details.*.id' => 'required|integer|exists:canvas_session_details,id',
            'details.*.qty_kembali' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $canvasSession) {
                // Find salesman details for stock logging
                $salesman = User::where('nik', $canvasSession->kode_sales)->first();
                $salesName = $salesman ? $salesman->name : $canvasSession->kode_sales;

                foreach ($request->details as $item) {
                    $detail = CanvasSessionDetail::findOrFail($item['id']);
                    $qtyKembali = (float)$item['qty_kembali'];

                    $satuan = BarangSatuan::findOrFail($detail->satuan_id);
                    $qtyKembaliSmallest = $qtyKembali * ($satuan->isi ?? 1);

                    // Check that returned qty doesn't exceed taken qty
                    if ($qtyKembali > (float)$detail->qty_ambil) {
                        $barang = Barang::find($detail->kode_barang);
                        $barangName = $barang ? $barang->nama_barang : $detail->kode_barang;
                        throw new \Exception("Jumlah kembali untuk '{$barangName}' ({$qtyKembali}) melebihi jumlah ambil ({$detail->qty_ambil})!");
                    }

                    // Replenish warehouse stock by returned quantity and log mutation
                    if ($qtyKembaliSmallest > 0) {
                        StokMutasi::log(
                            $detail->kode_barang,
                            now()->toDateString(),
                            'Canvas Kembali',
                            $canvasSession->no_canvas,
                            $qtyKembaliSmallest,
                            0,
                            Auth::id(),
                            'Pengembalian Canvas: ' . $salesName
                        );
                    }

                    // Update detail record
                    $detail->qty_kembali = $qtyKembali;
                    // Calculate discrepancy
                    $detail->selisih = (float)$detail->qty_ambil - (float)$detail->qty_terjual - $qtyKembali;
                    $detail->save();
                }

                // Update session header
                $canvasSession->status = 'completed';
                if ($request->filled('keterangan')) {
                    $canvasSession->keterangan = $request->keterangan;
                }
                $canvasSession->save();
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
