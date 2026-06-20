<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReturPembelian;
use App\Models\ReturPembelianDetail;
use App\Models\Pembelian;
use App\Models\Supplier;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;

class ReturPembelianController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturPembelian::with(['supplier', 'pembelian']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('no_retur', 'like', '%' . $request->search . '%')
                    ->orWhere('no_faktur', 'like', '%' . $request->search . '%')
                    ->orWhereHas('supplier', function ($sq) use ($request) {
                        $sq->where('nama_supplier', 'like', '%' . $request->search . '%');
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

        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        $items = $query->orderBy('tanggal', 'desc')->paginate(10)->appends($request->query());
        return view('retur_pembelian.index', compact('items'));
    }

    public function create()
    {
        $item = new ReturPembelian();
        $suppliers = Supplier::where('status', 1)->get();
        $pembelians = Pembelian::orderBy('tanggal', 'desc')->get();
        $barangs = Barang::where('status', 1)->with('satuans')->get();

        // Auto-generate RETP-YYYYMMDD-0001
        $today = date('Ymd');
        $last = ReturPembelian::where('no_retur', 'like', 'RETP-' . $today . '%')
            ->orderBy('no_retur', 'desc')
            ->first();

        if ($last) {
            $lastNumber = intval(substr($last->no_retur, -4));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        $item->no_retur = 'RETP-' . $today . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('retur_pembelian.form', compact('item', 'suppliers', 'pembelians', 'barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_retur' => 'required|string|unique:retur_pembelian,no_retur',
            'tanggal' => 'required|date',
            'jenis_retur' => 'required|string',
            'kode_supplier' => 'required|string|exists:supplier,kode_supplier',
            'no_faktur' => 'nullable|string|exists:pembelian,no_faktur',
            'kondisi' => 'required|string',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|exists:barang,kode_barang',
            'items.*.satuan_id' => 'required|integer',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.harga_retur' => 'required|numeric|min:0',
        ]);

        // Validate quantities against original invoice if provided
        if ($request->filled('no_faktur')) {
            $pembelian = Pembelian::with('details')->findOrFail($request->no_faktur);
            foreach ($request->items as $index => $row) {
                $pembDetail = $pembelian->details->where('kode_barang', $row['kode_barang'])->first();
                if (!$pembDetail) {
                    return redirect()->back()->withInput()->with('error', "Barang dengan kode {$row['kode_barang']} tidak ada di faktur pembelian.");
                }

                // Sum already returned qty for this barang from all OTHER returns
                $returnedQty = DB::table('retur_pembelian_detail')
                    ->join('retur_pembelian', 'retur_pembelian_detail.no_retur', '=', 'retur_pembelian.no_retur')
                    ->where('retur_pembelian.no_faktur', $request->no_faktur)
                    ->where('retur_pembelian_detail.kode_barang', $row['kode_barang'])
                    ->sum('retur_pembelian_detail.qty');

                $maxAvailable = $pembDetail->qty - $returnedQty;
                if ($row['qty'] > $maxAvailable) {
                    return redirect()->back()->withInput()->with('error', "Jumlah retur untuk barang {$row['kode_barang']} melebihi sisa yang bisa diretur (Maksimal: {$maxAvailable}).");
                }
            }
        }

        DB::transaction(function () use ($request) {
            $totalRetur = 0;
            $details = [];

            foreach ($request->items as $row) {
                // Decrement stock
                $satuan = \App\Models\BarangSatuan::findOrFail($row['satuan_id']);
                $qtySmallest = $row['qty'] * ($satuan->isi ?? 1);
                Barang::where('kode_barang', $row['kode_barang'])->decrement('stok', $qtySmallest);

                $subtotal = $row['qty'] * $row['harga_retur'];
                $totalRetur += $subtotal;

                $details[] = new ReturPembelianDetail([
                    'kode_barang' => $row['kode_barang'],
                    'satuan_id' => $row['satuan_id'],
                    'qty' => $row['qty'],
                    'harga_retur' => $row['harga_retur'],
                    'subtotal_retur' => $subtotal,
                ]);
            }

            $retur = ReturPembelian::create([
                'no_retur' => $request->no_retur,
                'tanggal' => $request->tanggal,
                'jenis_retur' => $request->jenis_retur,
                'kode_supplier' => $request->kode_supplier,
                'no_faktur' => $request->no_faktur,
                'kondisi' => $request->kondisi,
                'keterangan' => $request->keterangan,
                'total' => $totalRetur,
                'user_id' => auth()->id() ?? '1',
            ]);

            $retur->details()->saveMany($details);

            \App\Models\ActivityLog::create([
                'user_id' => auth()->id() ?? 1,
                'action' => 'Input Retur Pembelian',
                'description' => 'Input Retur Pembelian No Retur ' . $retur->no_retur,
                'ip_address' => $request->ip(),
                'no_faktur' => $retur->no_retur,
            ]);
        });

        return redirect()->route('retur-pembelian.index')->with('success', 'Transaksi retur pembelian berhasil disimpan');
    }

    public function show($no_retur)
    {
        $item = ReturPembelian::with(['supplier', 'pembelian', 'details.barang', 'details.barangSatuan'])->findOrFail($no_retur);
        return view('retur_pembelian.show', compact('item'));
    }

    public function edit($no_retur)
    {
        $item = ReturPembelian::with(['details.barang'])->findOrFail($no_retur);
        $suppliers = Supplier::where('status', 1)->get();
        $pembelians = Pembelian::orderBy('tanggal', 'desc')->get();
        $barangs = Barang::where('status', 1)->with('satuans')->get();
        return view('retur_pembelian.form', compact('item', 'suppliers', 'pembelians', 'barangs'));
    }

    public function update(Request $request, $no_retur)
    {
        $retur = ReturPembelian::with('details')->findOrFail($no_retur);

        $request->validate([
            'tanggal' => 'required|date',
            'jenis_retur' => 'required|string',
            'kode_supplier' => 'required|string|exists:supplier,kode_supplier',
            'no_faktur' => 'nullable|string|exists:pembelian,no_faktur',
            'kondisi' => 'required|string',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|exists:barang,kode_barang',
            'items.*.satuan_id' => 'required|integer',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.harga_retur' => 'required|numeric|min:0',
        ]);

        if ($request->filled('no_faktur')) {
            $pembelian = Pembelian::with('details')->findOrFail($request->no_faktur);
            foreach ($request->items as $index => $row) {
                $pembDetail = $pembelian->details->where('kode_barang', $row['kode_barang'])->first();
                if (!$pembDetail) {
                    return redirect()->back()->withInput()->with('error', "Barang dengan kode {$row['kode_barang']} tidak ada di faktur pembelian.");
                }

                // Sum already returned qty for this barang from all OTHER returns (excluding current retur)
                $returnedQty = DB::table('retur_pembelian_detail')
                    ->join('retur_pembelian', 'retur_pembelian_detail.no_retur', '=', 'retur_pembelian.no_retur')
                    ->where('retur_pembelian.no_faktur', $request->no_faktur)
                    ->where('retur_pembelian.no_retur', '!=', $no_retur)
                    ->where('retur_pembelian_detail.kode_barang', $row['kode_barang'])
                    ->sum('retur_pembelian_detail.qty');

                $maxAvailable = $pembDetail->qty - $returnedQty;
                if ($row['qty'] > $maxAvailable) {
                    return redirect()->back()->withInput()->with('error', "Jumlah retur untuk barang {$row['kode_barang']} melebihi sisa yang bisa diretur (Maksimal: {$maxAvailable}).");
                }
            }
        }

        DB::transaction(function () use ($request, $retur) {
            // Revert old return stock reductions (add back to stock)
            foreach ($retur->details as $oldDetail) {
                $oldSatuan = \App\Models\BarangSatuan::find($oldDetail->satuan_id);
                $oldQtySmallest = $oldDetail->qty * ($oldSatuan->isi ?? 1);
                Barang::where('kode_barang', $oldDetail->kode_barang)->increment('stok', $oldQtySmallest);
            }

            $totalRetur = 0;
            $details = [];

            foreach ($request->items as $row) {
                // Decrement stock for new return details
                $satuan = \App\Models\BarangSatuan::findOrFail($row['satuan_id']);
                $qtySmallest = $row['qty'] * ($satuan->isi ?? 1);
                Barang::where('kode_barang', $row['kode_barang'])->decrement('stok', $qtySmallest);

                $subtotal = $row['qty'] * $row['harga_retur'];
                $totalRetur += $subtotal;

                $details[] = new ReturPembelianDetail([
                    'kode_barang' => $row['kode_barang'],
                    'satuan_id' => $row['satuan_id'],
                    'qty' => $row['qty'],
                    'harga_retur' => $row['harga_retur'],
                    'subtotal_retur' => $subtotal,
                ]);
            }

            $retur->update([
                'tanggal' => $request->tanggal,
                'jenis_retur' => $request->jenis_retur,
                'kode_supplier' => $request->kode_supplier,
                'no_faktur' => $request->no_faktur,
                'kondisi' => $request->kondisi,
                'keterangan' => $request->keterangan,
                'total' => $totalRetur,
            ]);

            // Recreate details
            $retur->details()->delete();
            $retur->details()->saveMany($details);

            \App\Models\ActivityLog::create([
                'user_id' => auth()->id() ?? 1,
                'action' => 'Edit Retur Pembelian',
                'description' => 'Edit Retur Pembelian No Retur ' . $retur->no_retur,
                'ip_address' => $request->ip(),
                'no_faktur' => $retur->no_retur,
            ]);
        });

        return redirect()->route('retur-pembelian.index')->with('success', 'Transaksi retur pembelian berhasil diperbarui');
    }

    public function destroy($no_retur)
    {
        $retur = ReturPembelian::with('details')->findOrFail($no_retur);
        DB::transaction(function () use ($retur) {
            foreach ($retur->details as $detail) {
                $satuan = \App\Models\BarangSatuan::find($detail->satuan_id);
                $qtySmallest = $detail->qty * ($satuan->isi ?? 1);
                Barang::where('kode_barang', $detail->kode_barang)->increment('stok', $qtySmallest);
            }
            $retur->delete();

            \App\Models\ActivityLog::create([
                'user_id' => auth()->id() ?? 1,
                'action' => 'Hapus Retur Pembelian',
                'description' => 'Hapus Retur Pembelian No Retur ' . $retur->no_retur,
                'ip_address' => request()->ip(),
                'no_faktur' => $retur->no_retur,
            ]);
        });
        return redirect()->route('retur-pembelian.index')->with('success', 'Transaksi retur pembelian berhasil dihapus');
    }
}
