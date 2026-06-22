<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\Wilayah;
use App\Models\SubWilayah;


class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $query = Pelanggan::with(['wilayah', 'subWilayah']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_pelanggan', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_pelanggan', 'like', '%' . $request->search . '%')
                    ->orWhere('no_hp_pelanggan', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('kode_wilayah')) {
            $query->where('kode_wilayah', $request->kode_wilayah);
        }

        if ($request->filled('sub_wilayah')) {
            $query->where('sub_wilayah', $request->sub_wilayah);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('approve')) {
            if ($request->approve === 'pending') {
                $query->where(function ($q) {
                    $q->whereNull('approve')->orWhere('approve', 0);
                });
            } else {
                $query->where('approve', $request->approve);
            }
        }

        $pelanggans = $query->paginate(10)->appends($request->query());
        $wilayahs = Wilayah::all();
        $subWilayahs = SubWilayah::all();
        return view('master.pelanggan.index', compact('pelanggans', 'wilayahs', 'subWilayahs'));
    }

    public function create()
    {
        $item = new Pelanggan();
        $wilayahs = Wilayah::all();
        $subWilayahs = SubWilayah::all();
        return view('master.pelanggan.form', compact('item', 'wilayahs', 'subWilayahs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_pelanggan' => 'required',
            'nama_pelanggan' => 'required',
            'alamat_pelanggan' => 'required',
            'no_hp_pelanggan' => 'required',
            'limit_pelanggan' => 'required',
            'metode_bayar' => 'required',
            'kode_wilayah' => 'nullable|integer',
            'sub_wilayah' => 'nullable|integer',
            'status' => 'required|integer',
            'jenis_pelanggan' => 'nullable|string|max:30'
        ]);
        
        $data['approve'] = 1; // Default to approved for desktop admin creation
        
        \App\Models\Pelanggan::create($data);
        
        return redirect()->route('pelanggan.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        $item = Pelanggan::findOrFail($id);
        $wilayahs = Wilayah::all();
        $subWilayahs = SubWilayah::all();
        return view('master.pelanggan.form', compact('item', 'wilayahs', 'subWilayahs'));
    }

    public function update(Request $request, $id)
    {
        $row = Pelanggan::findOrFail($id);

        $row->update($request->validate([
            'kode_pelanggan' => 'required',
            'nama_pelanggan' => 'required',
            'alamat_pelanggan' => 'required',
            'no_hp_pelanggan' => 'required',
            'limit_pelanggan' => 'required',
            'metode_bayar' => 'required',
            'kode_wilayah' => 'nullable|integer',
            'sub_wilayah' => 'nullable|integer',
            'status' => 'required|integer',
            'jenis_pelanggan' => 'nullable|string|max:30'
        ]));
        
        return redirect()->route('pelanggan.index')->with('success', 'Data berhasil diubah');
    }

    public function destroy($id)
    {
        Pelanggan::findOrFail($id)->delete();
        return redirect()->route('pelanggan.index')->with('success', 'Data berhasil dihapus');
    }

    public function toggleStatus($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->status = $pelanggan->status == 1 ? 0 : 1;
        $pelanggan->save();

        return redirect()->back()->with('success', 'Status pelanggan berhasil diubah');
    }

    public function search(Request $request)
    {
        $search = $request->input('q');
        $excludeNoFaktur = $request->input('exclude_no_faktur');
        $today = now()->toDateString();

        $outstandingSubquery = \Illuminate\Support\Facades\DB::table('penjualan')
            ->selectRaw("COALESCE(SUM(CASE WHEN grand_total - (
                COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran WHERE penjualan_pembayaran.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) +
                COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_transfer WHERE penjualan_pembayaran_transfer.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) +
                COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_giro WHERE penjualan_pembayaran_giro.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) +
                COALESCE((SELECT SUM(total) FROM retur_penjualan WHERE retur_penjualan.no_faktur = penjualan.no_faktur), 0)
            ) >= 1 THEN grand_total - (
                COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran WHERE penjualan_pembayaran.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) +
                COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_transfer WHERE penjualan_pembayaran_transfer.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) +
                COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_giro WHERE penjualan_pembayaran_giro.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) +
                COALESCE((SELECT SUM(total) FROM retur_penjualan WHERE retur_penjualan.no_faktur = penjualan.no_faktur), 0)
            ) ELSE 0 END), 0)")
            ->whereColumn('penjualan.kode_pelanggan', 'pelanggan.kode_pelanggan')
            ->where('penjualan.batal', 0);

        if ($excludeNoFaktur) {
            $outstandingSubquery->where('penjualan.no_faktur', '!=', $excludeNoFaktur);
        }

        $overdueSubquery = \Illuminate\Support\Facades\DB::table('penjualan')
            ->selectRaw('1')
            ->whereColumn('penjualan.kode_pelanggan', 'pelanggan.kode_pelanggan')
            ->whereIn('penjualan.jenis_transaksi', ['K', 'Kredit'])
            ->where('penjualan.batal', 0)
            ->whereRaw('DATE_ADD(penjualan.tanggal, INTERVAL COALESCE(pelanggan.ljt, 30) DAY) < ?', [$today])
            ->whereRaw("grand_total - (
                COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran WHERE penjualan_pembayaran.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) +
                COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_transfer WHERE penjualan_pembayaran_transfer.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) +
                COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_giro WHERE penjualan_pembayaran_giro.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) +
                COALESCE((SELECT SUM(total) FROM retur_penjualan WHERE retur_penjualan.no_faktur = penjualan.no_faktur), 0)
            ) >= 1");

        if ($excludeNoFaktur) {
            $overdueSubquery->where('penjualan.no_faktur', '!=', $excludeNoFaktur);
        }

        $query = Pelanggan::with(['wilayah', 'subWilayah'])
            ->where('status', 1);

        if ($request->filled('kode_wilayah')) {
            $query->where('kode_wilayah', $request->kode_wilayah);
        }

        if ($request->filled('sub_wilayah')) {
            $query->where('sub_wilayah', $request->sub_wilayah);
        }

        // Filter out unapproved customers for sales and spv sales
        $role = strtolower(auth()->user()->role ?? '');
        if (in_array($role, ['sales', 'spv sales']) || $request->has('only_approved')) {
            $query->where('approve', 1);
        }

        $query->select('pelanggan.*')
            ->addSelect([
                'outstanding_piutang' => $outstandingSubquery,
                'has_overdue' => $overdueSubquery->limit(1)
            ]);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_pelanggan', 'like', "%{$search}%")
                  ->orWhere('kode_pelanggan', 'like', "%{$search}%");
            });
        }

        $pelanggans = $query->orderBy('nama_pelanggan')
            ->limit(30)
            ->get();

        $results = [];
        foreach ($pelanggans as $p) {
            $sisaLimit = max(0, $p->limit_pelanggan - ($p->outstanding_piutang ?? 0));
            $hasOverdue = ($p->jenis_pelanggan !== '1' && $p->has_overdue !== null);

            $wilayahText = ($p->wilayah ? $p->wilayah->nama_wilayah : '-');
            if ($p->subWilayah) {
                $wilayahText .= ' / ' . $p->subWilayah->nama_wilayah;
            }

            $overdueInvoices = [];
            if ($hasOverdue) {
                $overdueInvoices = $p->getOverdueInvoices($excludeNoFaktur)->pluck('no_faktur')->toArray();
            }

            $results[] = [
                'id' => $p->kode_pelanggan,
                'text' => $p->nama_pelanggan . ' (' . $p->kode_pelanggan . ')',
                'nama' => $p->nama_pelanggan,
                'kode' => $p->kode_pelanggan,
                'hp' => $p->no_hp_pelanggan ?: '-',
                'alamat' => $p->alamat_pelanggan ?: '-',
                'wilayah' => $wilayahText,
                'metode' => $p->metode_bayar ?: '-',
                'limit' => $p->limit_pelanggan,
                'sisa_limit' => $sisaLimit,
                'has_overdue' => $hasOverdue ? 1 : 0,
                'overdue_invoices' => $overdueInvoices
            ];
        }

        return response()->json($results);
    }

    public function map(Request $request)
    {
        $query = Pelanggan::with(['wilayah', 'subWilayah'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', '')
            ->where('longitude', '!=', '');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_pelanggan', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_pelanggan', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('kode_wilayah')) {
            $query->where('kode_wilayah', $request->kode_wilayah);
        }

        if ($request->filled('sub_wilayah')) {
            $query->where('sub_wilayah', $request->sub_wilayah);
        }

        $pelanggans = $query->orderBy('nama_pelanggan')->get();
        $wilayahs = Wilayah::all();
        $subWilayahs = SubWilayah::all();

        return view('master.pelanggan.map', compact('pelanggans', 'wilayahs', 'subWilayahs', 'request'));
    }

    public function approve($kode_pelanggan)
    {
        $pelanggan = Pelanggan::findOrFail($kode_pelanggan);
        $pelanggan->update([
            'approve' => 1,
            'status' => 1
        ]);
        return redirect()->back()->with('success', "Pelanggan '{$pelanggan->nama_pelanggan}' berhasil disetujui!");
    }

    public function reject($kode_pelanggan)
    {
        $pelanggan = Pelanggan::findOrFail($kode_pelanggan);
        $pelanggan->update([
            'approve' => 2
        ]);
        return redirect()->back()->with('success', "Pendaftaran pelanggan '{$pelanggan->nama_pelanggan}' ditolak.");
    }
}
