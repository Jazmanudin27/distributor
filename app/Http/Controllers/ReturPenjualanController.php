<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReturPenjualan;
use App\Models\ReturPenjualanDetail;
use App\Models\Penjualan;
use App\Models\Pelanggan;
use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\PenjualanPembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReturPenjualanController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturPenjualan::with(['pelanggan', 'penjualan', 'sales']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('no_retur', 'like', '%' . $request->search . '%')
                    ->orWhere('no_faktur', 'like', '%' . $request->search . '%')
                    ->orWhereHas('pelanggan', function ($sq) use ($request) {
                        $sq->where('nama_pelanggan', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal', '<=', $request->tanggal_akhir);
        }

        if ($request->filled('jenis_retur')) {
            $query->where('jenis_retur', $request->jenis_retur);
        }

        $items = $query->orderBy('tanggal', 'desc')->paginate(15)->appends($request->query());
        return view('retur_penjualan.index', compact('items'));
    }

    public function create()
    {
        $item = new ReturPenjualan();
        $pelanggans = collect();
        $penjualans = Penjualan::orderBy('tanggal', 'desc')->get();
        $barangs = Barang::where('status', 1)->with('satuans')->orderBy('nama_barang')->get();

        // Auto-generate RETS-YYYYMMDD-0001
        $today = date('Ymd');
        $last = ReturPenjualan::where('no_retur', 'like', 'RETS-' . $today . '%')
            ->orderBy('no_retur', 'desc')
            ->first();

        $nextNumber = $last ? (intval(substr($last->no_retur, -4)) + 1) : 1;
        $item->no_retur = 'RETS-' . $today . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('retur_penjualan.form', compact('item', 'pelanggans', 'penjualans', 'barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_retur'        => 'required|string|unique:retur_penjualan,no_retur',
            'tanggal'         => 'required|date',
            'jenis_retur'     => 'required|string',
            'kode_pelanggan'  => 'required|string|exists:pelanggan,kode_pelanggan',
            'no_faktur'       => 'nullable|string|exists:penjualan,no_faktur',
            'keterangan'      => 'nullable|string',
            'items'           => 'required|array|min:1',
            'items.*.kode_barang' => 'required|exists:barang,kode_barang',
            'items.*.satuan_id'   => 'required|integer',
            'items.*.qty'         => 'required|numeric|min:0.01',
            'items.*.harga_retur' => 'required|numeric|min:0',
            'items.*.diskon1_persen' => 'nullable|numeric|min:0|max:100',
            'items.*.diskon2_persen' => 'nullable|numeric|min:0|max:100',
            'items.*.diskon3_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        // Validate quantities against original invoice if provided
        if ($request->filled('no_faktur')) {
            $penjualan = Penjualan::with('details')->findOrFail($request->no_faktur);
            foreach ($request->items as $row) {
                $penjDetail = $penjualan->details->where('kode_barang', $row['kode_barang'])->first();
                if (!$penjDetail) {
                    return redirect()->back()->withInput()->with('error', "Barang dengan kode {$row['kode_barang']} tidak ada di faktur penjualan.");
                }

                // Sum already returned qty for this barang from all OTHER returns
                $returnedQty = DB::table('retur_penjualan_detail')
                    ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
                    ->where('retur_penjualan.no_faktur', $request->no_faktur)
                    ->where('retur_penjualan_detail.kode_barang', $row['kode_barang'])
                    ->sum('retur_penjualan_detail.qty');

                $maxAvailable = $penjDetail->qty - $returnedQty;
                if ($row['qty'] > $maxAvailable) {
                    return redirect()->back()->withInput()->with('error', "Jumlah retur untuk barang {$row['kode_barang']} melebihi sisa penjualan (Maksimal: {$maxAvailable}).");
                }
            }
        }

        DB::transaction(function () use ($request) {
            $totalRetur = 0;
            $details = [];

            foreach ($request->items as $row) {
                // Increment stock (goods returned to warehouse)
                $satuan = BarangSatuan::findOrFail($row['satuan_id']);
                $qtySmallest = $row['qty'] * ($satuan->isi ?? 1);
                Barang::where('kode_barang', $row['kode_barang'])->increment('stok', $qtySmallest);

                $subtotal = $row['qty'] * $row['harga_retur'];
                $d1_pct   = floatval($row['diskon1_persen'] ?? 0);
                $d2_pct   = floatval($row['diskon2_persen'] ?? 0);
                $d3_pct   = floatval($row['diskon3_persen'] ?? 0);

                $d1 = $subtotal * ($d1_pct / 100);
                $d2 = ($subtotal - $d1) * ($d2_pct / 100);
                $d3 = ($subtotal - $d1 - $d2) * ($d3_pct / 100);

                $rowDiskon = round($d1 + $d2 + $d3, 2);
                $rowNett   = $subtotal - $rowDiskon;
                $totalRetur += $rowNett;

                $details[] = new ReturPenjualanDetail([
                    'kode_barang'         => $row['kode_barang'],
                    'id_satuan'           => $row['satuan_id'],
                    'qty'                 => $row['qty'],
                    'harga_retur'         => $row['harga_retur'],
                    'subtotal_retur'      => $subtotal,
                    'diskon1_persen'      => $d1_pct,
                    'diskon2_persen'      => $d2_pct,
                    'diskon3_persen'      => $d3_pct,
                    'total_diskon_rupiah' => $rowDiskon,
                ]);
            }

            $retur = ReturPenjualan::create([
                'no_retur'       => $request->no_retur,
                'tanggal'        => $request->tanggal,
                'jenis_retur'    => $request->jenis_retur,
                'kode_pelanggan' => $request->kode_pelanggan,
                'kode_sales'     => $request->kode_sales,
                'no_faktur'      => $request->no_faktur,
                'keterangan'     => $request->keterangan,
                'total'          => $totalRetur,
                'user_id'        => Auth::id() ?? 1,
            ]);

            $retur->details()->saveMany($details);

            // Create automatic payment record to deduct piutang if linked to an invoice
            if ($retur->no_faktur) {
                $kodeSales = $retur->kode_sales;
                if (!$kodeSales) {
                    $penj = Penjualan::find($retur->no_faktur);
                    $kodeSales = $penj ? $penj->kode_sales : null;
                }

                PenjualanPembayaran::create([
                    'no_bukti' => $retur->no_retur,
                    'tanggal' => $retur->tanggal,
                    'no_faktur' => $retur->no_faktur,
                    'kode_pelanggan' => $retur->kode_pelanggan,
                    'kode_sales' => $kodeSales,
                    'jenis_bayar' => 'Retur',
                    'jumlah' => $retur->total,
                    'keterangan' => 'Potong Piutang dari Retur: ' . $retur->no_retur,
                    'id_user' => Auth::id() ?? 1,
                    'status' => 'disetujui',
                ]);
            }

            \App\Models\ActivityLog::create([
                'user_id' => Auth::id() ?? 1,
                'action' => 'Input Retur',
                'description' => 'Input Retur No Retur ' . $retur->no_retur,
                'ip_address' => $request->ip(),
                'no_faktur' => $retur->no_retur,
            ]);
        });

        return redirect()->route('retur-penjualan.index')->with('success', 'Transaksi retur penjualan berhasil disimpan.');
    }

    public function show($no_retur)
    {
        $item = ReturPenjualan::with(['pelanggan.wilayah', 'penjualan', 'details.barang', 'details.barangSatuan', 'user'])->findOrFail($no_retur);
        return view('retur_penjualan.show', compact('item'));
    }

    public function print($no_retur)
    {
        $item = ReturPenjualan::with(['pelanggan.wilayah', 'details.barang', 'details.barangSatuan', 'user'])
            ->findOrFail($no_retur);

        \App\Models\ActivityLog::create([
            'user_id' => Auth::id() ?? 1,
            'action' => 'Cetak Faktur',
            'description' => 'Cetak Faktur ' . $no_retur,
            'ip_address' => request()->ip(),
            'no_faktur' => $no_retur,
        ]);

        return view('retur_penjualan.print', compact('item'));
    }

    public function edit($no_retur)
    {
        $item = ReturPenjualan::with(['details.barang', 'details.barangSatuan'])->findOrFail($no_retur);
        $pelanggans = Pelanggan::where('kode_pelanggan', $item->kode_pelanggan)->get();
        $penjualans = Penjualan::orderBy('tanggal', 'desc')->get();
        $barangs = Barang::where('status', 1)->with('satuans')->orderBy('nama_barang')->get();

        return view('retur_penjualan.form', compact('item', 'pelanggans', 'penjualans', 'barangs'));
    }

    public function update(Request $request, $no_retur)
    {
        $retur = ReturPenjualan::with('details')->findOrFail($no_retur);

        $request->validate([
            'tanggal'         => 'required|date',
            'jenis_retur'     => 'required|string',
            'kode_pelanggan'  => 'required|string|exists:pelanggan,kode_pelanggan',
            'no_faktur'       => 'nullable|string|exists:penjualan,no_faktur',
            'keterangan'      => 'nullable|string',
            'items'           => 'required|array|min:1',
            'items.*.kode_barang' => 'required|exists:barang,kode_barang',
            'items.*.satuan_id'   => 'required|integer',
            'items.*.qty'         => 'required|numeric|min:0.01',
            'items.*.harga_retur' => 'required|numeric|min:0',
            'items.*.diskon1_persen' => 'nullable|numeric|min:0|max:100',
            'items.*.diskon2_persen' => 'nullable|numeric|min:0|max:100',
            'items.*.diskon3_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        // Validate quantities against original invoice if provided
        if ($request->filled('no_faktur')) {
            $penjualan = Penjualan::with('details')->findOrFail($request->no_faktur);
            foreach ($request->items as $row) {
                $penjDetail = $penjualan->details->where('kode_barang', $row['kode_barang'])->first();
                if (!$penjDetail) {
                    return redirect()->back()->withInput()->with('error', "Barang dengan kode {$row['kode_barang']} tidak ada di faktur penjualan.");
                }

                // Sum already returned qty for this barang from all OTHER returns (excluding current retur)
                $returnedQty = DB::table('retur_penjualan_detail')
                    ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
                    ->where('retur_penjualan.no_faktur', $request->no_faktur)
                    ->where('retur_penjualan.no_retur', '!=', $no_retur)
                    ->where('retur_penjualan_detail.kode_barang', $row['kode_barang'])
                    ->sum('retur_penjualan_detail.qty');

                $maxAvailable = $penjDetail->qty - $returnedQty;
                if ($row['qty'] > $maxAvailable) {
                    return redirect()->back()->withInput()->with('error', "Jumlah retur untuk barang {$row['kode_barang']} melebihi sisa penjualan (Maksimal: {$maxAvailable}).");
                }
            }
        }

        DB::transaction(function () use ($request, $retur) {
            // Revert old return stock additions (decrement stock back)
            foreach ($retur->details as $oldDetail) {
                $oldSatuan = BarangSatuan::find($oldDetail->id_satuan);
                $oldQtySmallest = $oldDetail->qty * ($oldSatuan->isi ?? 1);
                Barang::where('kode_barang', $oldDetail->kode_barang)->decrement('stok', $oldQtySmallest);
            }

            $totalRetur = 0;
            $details = [];

            foreach ($request->items as $row) {
                // Increment stock with new return details
                $satuan = BarangSatuan::findOrFail($row['satuan_id']);
                $qtySmallest = $row['qty'] * ($satuan->isi ?? 1);
                Barang::where('kode_barang', $row['kode_barang'])->increment('stok', $qtySmallest);

                $subtotal = $row['qty'] * $row['harga_retur'];
                $d1_pct   = floatval($row['diskon1_persen'] ?? 0);
                $d2_pct   = floatval($row['diskon2_persen'] ?? 0);
                $d3_pct   = floatval($row['diskon3_persen'] ?? 0);

                $d1 = $subtotal * ($d1_pct / 100);
                $d2 = ($subtotal - $d1) * ($d2_pct / 100);
                $d3 = ($subtotal - $d1 - $d2) * ($d3_pct / 100);

                $rowDiskon = round($d1 + $d2 + $d3, 2);
                $rowNett   = $subtotal - $rowDiskon;
                $totalRetur += $rowNett;

                $details[] = new ReturPenjualanDetail([
                    'kode_barang'         => $row['kode_barang'],
                    'id_satuan'           => $row['satuan_id'],
                    'qty'                 => $row['qty'],
                    'harga_retur'         => $row['harga_retur'],
                    'subtotal_retur'      => $subtotal,
                    'diskon1_persen'      => $d1_pct,
                    'diskon2_persen'      => $d2_pct,
                    'diskon3_persen'      => $d3_pct,
                    'total_diskon_rupiah' => $rowDiskon,
                ]);
            }

            $retur->update([
                'tanggal'        => $request->tanggal,
                'jenis_retur'    => $request->jenis_retur,
                'kode_pelanggan' => $request->kode_pelanggan,
                'kode_sales'     => $request->kode_sales,
                'no_faktur'      => $request->no_faktur,
                'keterangan'     => $request->keterangan,
                'total'          => $totalRetur,
            ]);

            // Recreate details
            $retur->details()->delete();
            $retur->details()->saveMany($details);

            // Manage payment history
            $payment = PenjualanPembayaran::where('no_bukti', $retur->no_retur)->first();
            if ($retur->no_faktur) {
                $kodeSales = $retur->kode_sales;
                if (!$kodeSales) {
                    $penj = Penjualan::find($retur->no_faktur);
                    $kodeSales = $penj ? $penj->kode_sales : null;
                }

                if ($payment) {
                    $payment->update([
                        'tanggal' => $retur->tanggal,
                        'no_faktur' => $retur->no_faktur,
                        'kode_pelanggan' => $retur->kode_pelanggan,
                        'kode_sales' => $kodeSales,
                        'jumlah' => $retur->total,
                        'keterangan' => 'Potong Piutang dari Retur: ' . $retur->no_retur,
                    ]);
                } else {
                    PenjualanPembayaran::create([
                        'no_bukti' => $retur->no_retur,
                        'tanggal' => $retur->tanggal,
                        'no_faktur' => $retur->no_faktur,
                        'kode_pelanggan' => $retur->kode_pelanggan,
                        'kode_sales' => $kodeSales,
                        'jenis_bayar' => 'Retur',
                        'jumlah' => $retur->total,
                        'keterangan' => 'Potong Piutang dari Retur: ' . $retur->no_retur,
                        'id_user' => Auth::id() ?? 1,
                        'status' => 'disetujui',
                    ]);
                }
            } else {
                if ($payment) {
                    $payment->delete();
                }
            }

            \App\Models\ActivityLog::create([
                'user_id' => Auth::id() ?? 1,
                'action' => 'Edit Retur',
                'description' => 'Edit Retur No Retur ' . $retur->no_retur,
                'ip_address' => $request->ip(),
                'no_faktur' => $retur->no_retur,
            ]);
        });

        return redirect()->route('retur-penjualan.index')->with('success', 'Transaksi retur penjualan berhasil diperbarui.');
    }

    public function destroy($no_retur)
    {
        $retur = ReturPenjualan::with('details')->findOrFail($no_retur);

        DB::transaction(function () use ($retur) {
            // Revert stock additions (decrement stock)
            foreach ($retur->details as $detail) {
                $satuan = BarangSatuan::find($detail->id_satuan);
                $qtySmallest = $detail->qty * ($satuan->isi ?? 1);
                Barang::where('kode_barang', $detail->kode_barang)->decrement('stok', $qtySmallest);
            }
            // Delete associated payment record
            PenjualanPembayaran::where('no_bukti', $retur->no_retur)->delete();

            $retur->delete();

            \App\Models\ActivityLog::create([
                'user_id' => Auth::id() ?? 1,
                'action' => 'Hapus Retur',
                'description' => 'Hapus Retur No Retur ' . $retur->no_retur,
                'ip_address' => request()->ip(),
                'no_faktur' => $retur->no_retur,
            ]);
        });

        return redirect()->route('retur-penjualan.index')->with('success', 'Transaksi retur penjualan berhasil dihapus.');
    }
}
