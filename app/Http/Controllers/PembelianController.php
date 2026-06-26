<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\PembelianPembayaran;
use App\Models\Supplier;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PembelianController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembelian::with(['supplier', 'pembayarans']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('no_faktur', 'like', '%' . $request->search . '%')
                    ->orWhere('no_po', 'like', '%' . $request->search . '%')
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

        if ($request->filled('status_pembayaran')) {
            $status = $request->status_pembayaran;
            // Filter by Paid or Unpaid (Lunas / Belum Lunas)
            $query->where(function ($q) use ($status) {
                if ($status === 'lunas') {
                    $q->whereRaw('(SELECT COALESCE(SUM(jumlah), 0) FROM pembelian_pembayaran WHERE pembelian_pembayaran.no_faktur = pembelian.no_faktur) + (SELECT COALESCE(SUM(total), 0) FROM retur_pembelian WHERE retur_pembelian.no_faktur = pembelian.no_faktur AND jenis_retur = \'PF\') >= grand_total');
                } else {
                    $q->whereRaw('(SELECT COALESCE(SUM(jumlah), 0) FROM pembelian_pembayaran WHERE pembelian_pembayaran.no_faktur = pembelian.no_faktur) + (SELECT COALESCE(SUM(total), 0) FROM retur_pembelian WHERE retur_pembelian.no_faktur = pembelian.no_faktur AND jenis_retur = \'PF\') < grand_total');
                }
            });
        }

        $items = $query->orderBy('tanggal', 'desc')->paginate(10)->appends($request->query());
        return view('pembelian.index', compact('items'));
    }

    public function create()
    {
        $item = new Pembelian();
        $suppliers = Supplier::where('status', 1)->get();
        $barangs = Barang::where('status', 1)->with('satuans')->get();
        
        // Draft format TEMB-YYYYMMDD-0001
        $today = date('Ymd');
        $last = Pembelian::where('no_faktur', 'like', 'PEMB-' . $today . '%')
            ->orderBy('no_faktur', 'desc')
            ->first();
            
        if ($last) {
            $lastNumber = intval(substr($last->no_faktur, -4));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        $item->no_faktur = 'PEMB-' . $today . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        $existingDetails = [];
        
        return view('pembelian.form', compact('item', 'suppliers', 'barangs', 'existingDetails'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_po' => 'nullable|string',
            'tanggal' => 'required|date',
            'jatuh_tempo' => 'nullable|date',
            'kode_supplier' => 'required|string|exists:supplier,kode_supplier',
            'jenis_transaksi' => 'required|in:Kredit',
            'potongan' => 'required|numeric|min:0',
            'pajak' => 'required|numeric|min:0',
            'biaya_lain' => 'required|numeric|min:0',
            'potongan_claim' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|exists:barang,kode_barang',
            'items.*.satuan_id' => 'required|integer',
            'items.*.satuan' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.harga' => 'required|numeric|min:0',
            'items.*.diskon' => 'required|numeric|min:0',
        ]);

        $savedNoFaktur = DB::transaction(function () use ($request) {
            // === Generate no_faktur secara atomik (mencegah race condition) ===
            $today = date('Ymd');
            $lastFaktur = Pembelian::where('no_faktur', 'like', 'PEMB-' . $today . '%')
                ->lockForUpdate()
                ->orderBy('no_faktur', 'desc')
                ->first();
            $nextNum = $lastFaktur ? (intval(substr($lastFaktur->no_faktur, -4)) + 1) : 1;
            $noFaktur = 'PEMB-' . $today . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $subtotalSum = 0;
            $details = [];

            foreach ($request->items as $row) {
                $satuan = \App\Models\BarangSatuan::findOrFail($row['satuan_id']);
                $qtySmallest = $row['qty'] * ($satuan->isi ?? 1);
                \App\Models\StokMutasi::log($row['kode_barang'], $request->tanggal, 'Pembelian', $noFaktur, $qtySmallest, 0);

                $total = ($row['qty'] * $row['harga']) - $row['diskon'];
                $subtotal = $row['qty'] * $row['harga'];
                $subtotalSum += $subtotal;

                $details[] = new PembelianDetail([
                    'kode_barang' => $row['kode_barang'],
                    'satuan_id' => $row['satuan_id'],
                    'satuan' => $row['satuan'],
                    'qty' => $row['qty'],
                    'harga' => $row['harga'],
                    'diskon' => $row['diskon'],
                    'subtotal' => $subtotal,
                    'total' => $total,
                ]);
            }

            // grand_total = subtotalSum - potongan - potongan_claim + pajak + biaya_lain
            $grandTotal = $subtotalSum - $request->potongan - $request->potongan_claim + $request->pajak + $request->biaya_lain;

            $pembelian = Pembelian::create([
                'no_faktur' => $noFaktur,
                'no_po' => $request->no_po,
                'tanggal' => $request->tanggal,
                'jatuh_tempo' => $request->jatuh_tempo,
                'kode_supplier' => $request->kode_supplier,
                'jenis_transaksi' => $request->jenis_transaksi,
                'potongan' => $request->potongan,
                'pajak' => $request->pajak,
                'biaya_lain' => $request->biaya_lain,
                'potongan_claim' => $request->potongan_claim,
                'grand_total' => $grandTotal,
                'keterangan' => $request->keterangan,
                'id_user' => auth()->id() ?? '1',
            ]);

            $pembelian->details()->saveMany($details);

            \App\Models\ActivityLog::create([
                'user_id' => auth()->id() ?? 1,
                'action' => 'Tambah Pembelian',
                'description' => $pembelian->no_faktur . ' (Supplier: ' . $pembelian->kode_supplier . ')',
                'ip_address' => $request->ip(),
                'no_faktur' => $pembelian->no_faktur,
            ]);

            return $pembelian->no_faktur;
        });

        return redirect()->route('pembelian.index')->with('success', 'Transaksi pembelian ' . $savedNoFaktur . ' berhasil disimpan');
    }

    public function show($no_faktur)
    {
        $item = Pembelian::with(['supplier', 'details.barang', 'pembayarans'])->findOrFail($no_faktur);
        $totalBayar = $item->pembayarans->sum('jumlah');
        
        $returs = \App\Models\ReturPembelian::where('no_faktur', $no_faktur)
            ->where('jenis_retur', 'PF')
            ->get();
        $totalRetur = $returs->sum('total');

        $sisaBayar = $item->grand_total - $totalBayar - $totalRetur;
        return view('pembelian.show', compact('item', 'totalBayar', 'sisaBayar', 'returs', 'totalRetur'));
    }

    public function edit($no_faktur)
    {
        $item = Pembelian::with(['details.barang'])->findOrFail($no_faktur);
        $suppliers = Supplier::where('status', 1)->get();
        $barangs = Barang::where('status', 1)->with('satuans')->get();

        // Build clean simplified array for JS (avoids Eloquent collection serialization issues)
        $existingDetails = $item->details->map(function ($d) {
            return [
                'kode_barang' => $d->kode_barang,
                'nama_barang' => $d->barang ? $d->barang->nama_barang : 'Barang',
                'satuan_id'   => $d->satuan_id,
                'satuan'      => $d->satuan,
                'qty'         => $d->qty,
                'harga'       => (int) $d->harga,
                'diskon'      => (int) $d->diskon,
            ];
        })->values()->toArray();

        return view('pembelian.form', compact('item', 'suppliers', 'barangs', 'existingDetails'));
    }

    public function update(Request $request, $no_faktur)
    {
        $pembelian = Pembelian::with('details')->findOrFail($no_faktur);

        $request->validate([
            'no_po' => 'nullable|string',
            'tanggal' => 'required|date',
            'jatuh_tempo' => 'nullable|date',
            'kode_supplier' => 'required|string|exists:supplier,kode_supplier',
            'jenis_transaksi' => 'required|in:Kredit',
            'potongan' => 'required|numeric|min:0',
            'pajak' => 'required|numeric|min:0',
            'biaya_lain' => 'required|numeric|min:0',
            'potongan_claim' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|exists:barang,kode_barang',
            'items.*.satuan_id' => 'required|integer',
            'items.*.satuan' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.harga' => 'required|numeric|min:0',
            'items.*.diskon' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $pembelian) {
            // Revert old details stock additions
            foreach ($pembelian->details as $oldDetail) {
                $oldSatuan = \App\Models\BarangSatuan::find($oldDetail->satuan_id);
                $oldQtySmallest = $oldDetail->qty * ($oldSatuan->isi ?? 1);
                \App\Models\StokMutasi::log($oldDetail->kode_barang, $request->tanggal, 'Batal Pembelian (Edit)', $pembelian->no_faktur, 0, $oldQtySmallest);
            }

            $subtotalSum = 0;
            $details = [];

            foreach ($request->items as $row) {
                // Increment new stock
                $satuan = \App\Models\BarangSatuan::findOrFail($row['satuan_id']);
                $qtySmallest = $row['qty'] * ($satuan->isi ?? 1);
                \App\Models\StokMutasi::log($row['kode_barang'], $request->tanggal, 'Pembelian (Edit)', $pembelian->no_faktur, $qtySmallest, 0);

                $total = ($row['qty'] * $row['harga']) - $row['diskon'];
                $subtotal = $row['qty'] * $row['harga'];
                $subtotalSum += $subtotal;

                $details[] = new PembelianDetail([
                    'kode_barang' => $row['kode_barang'],
                    'satuan_id' => $row['satuan_id'],
                    'satuan' => $row['satuan'],
                    'qty' => $row['qty'],
                    'harga' => $row['harga'],
                    'diskon' => $row['diskon'],
                    'subtotal' => $subtotal,
                    'total' => $total,
                ]);
            }

            $grandTotal = $subtotalSum - $request->potongan - $request->potongan_claim + $request->pajak + $request->biaya_lain;

            $pembelian->update([
                'no_po' => $request->no_po,
                'tanggal' => $request->tanggal,
                'jatuh_tempo' => $request->jatuh_tempo,
                'kode_supplier' => $request->kode_supplier,
                'jenis_transaksi' => $request->jenis_transaksi,
                'potongan' => $request->potongan,
                'pajak' => $request->pajak,
                'biaya_lain' => $request->biaya_lain,
                'potongan_claim' => $request->potongan_claim,
                'grand_total' => $grandTotal,
                'keterangan' => $request->keterangan,
            ]);

            // Recreate details
            $pembelian->details()->delete();
            $pembelian->details()->saveMany($details);

            \App\Models\ActivityLog::create([
                'user_id' => auth()->id() ?? 1,
                'action' => 'Edit Pembelian',
                'description' => $no_faktur,
                'ip_address' => $request->ip(),
                'no_faktur' => $no_faktur,
            ]);
        });

        return redirect()->route('pembelian.index')->with('success', 'Transaksi pembelian berhasil diperbarui');
    }

    public function destroy($no_faktur)
    {
        $pembelian = Pembelian::with('details')->findOrFail($no_faktur);
        DB::transaction(function () use ($pembelian, $no_faktur) {
            foreach ($pembelian->details as $detail) {
                $satuan = \App\Models\BarangSatuan::find($detail->satuan_id);
                $qtySmallest = $detail->qty * ($satuan->isi ?? 1);
                \App\Models\StokMutasi::log($detail->kode_barang, now()->toDateString(), 'Batal Pembelian (Hapus)', $pembelian->no_faktur, 0, $qtySmallest);
            }
            $pembelian->delete();

            \App\Models\ActivityLog::create([
                'user_id' => auth()->id() ?? 1,
                'action' => 'Hapus Pembelian',
                'description' => $no_faktur,
                'ip_address' => request()->ip(),
                'no_faktur' => $no_faktur,
            ]);
        });
        return redirect()->route('pembelian.index')->with('success', 'Transaksi pembelian berhasil dihapus');
    }

    public function storePayment(Request $request, $no_faktur)
    {
        $item = Pembelian::findOrFail($no_faktur);

        $request->validate([
            'no_bukti' => 'nullable|string|max:50',
            'tanggal' => 'required|date',
            'jenis_bayar' => 'required|string|max:50',
            'jumlah' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string',
        ]);

        // Validate that payment does not exceed sisaBayar
        $totalBayar = $item->pembayarans->sum('jumlah');
        $totalRetur = \App\Models\ReturPembelian::where('no_faktur', $no_faktur)
            ->where('jenis_retur', 'PF')
            ->sum('total');
        $sisaBayar = $item->grand_total - $totalBayar - $totalRetur;

        if ($request->jumlah > $sisaBayar) {
            return redirect()->back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan! Sisa tagihan saat ini: Rp ' . number_format($sisaBayar, 0, ',', '.'));
        }

        // Auto-generate no_bukti if empty
        $no_bukti = $request->no_bukti;
        if (empty($no_bukti)) {
            $today = date('Ymd');
            $last = PembelianPembayaran::where('no_bukti', 'like', 'BKM-PEMB-' . $today . '%')->orderBy('id', 'desc')->first();
            if ($last) {
                $lastNumber = intval(substr($last->no_bukti, -4));
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }
            $no_bukti = 'BKM-PEMB-' . $today . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }

        PembelianPembayaran::create([
            'no_bukti' => $no_bukti,
            'tanggal' => $request->tanggal,
            'no_faktur' => $no_faktur,
            'jenis_bayar' => $request->jenis_bayar,
            'jumlah' => $request->jumlah,
            'keterangan' => $request->keterangan,
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'Input Pembayaran ' . $request->jenis_bayar,
            'description' => $no_bukti,
            'ip_address' => $request->ip(),
            'no_faktur' => $no_faktur,
        ]);

        return redirect()->route('pembelian.show', $no_faktur)->with('success', 'Pembayaran berhasil disimpan');
    }

    public function approve($no_faktur)
    {
        $pembelian = Pembelian::findOrFail($no_faktur);

        if ($pembelian->tanggal_approve) {
            return back()->with('error', 'Transaksi pembelian ini sudah disetujui sebelumnya.');
        }

        $pembelian->update([
            'tanggal_approve' => now(),
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'Approve Pembelian',
            'description' => $pembelian->no_faktur . ' disetujui.',
            'ip_address' => request()->ip(),
            'no_faktur' => $pembelian->no_faktur,
        ]);

        return back()->with('success', 'Transaksi pembelian berhasil disetujui.');
    }

    public function getPurchaseItems($no_faktur)
    {
        $pembelian = Pembelian::with(['details.barang', 'details.barangSatuan', 'supplier'])->find($no_faktur);
        if (!$pembelian) {
            return response()->json(['message' => 'Faktur tidak ditemukan'], 404);
        }
        return response()->json([
            'kode_supplier' => $pembelian->kode_supplier,
            'supplier_name' => $pembelian->supplier->nama_supplier ?? '-',
            'items' => $pembelian->details->map(function ($detail) use ($no_faktur) {
                // Sum already returned qty for this barang from all returns of this faktur
                $returnedQty = DB::table('retur_pembelian_detail')
                    ->join('retur_pembelian', 'retur_pembelian_detail.no_retur', '=', 'retur_pembelian.no_retur')
                    ->where('retur_pembelian.no_faktur', $no_faktur)
                    ->where('retur_pembelian_detail.kode_barang', $detail->kode_barang)
                    ->sum('retur_pembelian_detail.qty');

                return [
                    'kode_barang' => $detail->kode_barang,
                    'nama_barang' => $detail->barang->nama_barang ?? '-',
                    'satuan_id' => $detail->satuan_id,
                    'satuan' => $detail->satuan,
                    'qty_beli' => $detail->qty,
                    'qty_retur_sebelumnya' => (float)$returnedQty,
                    'qty' => max(0, $detail->qty - (float)$returnedQty),
                    'harga' => $detail->harga,
                    'diskon' => $detail->diskon,
                    'total' => $detail->total,
                ];
            })
        ]);
    }
}
