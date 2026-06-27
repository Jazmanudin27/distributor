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
        $query = Pelanggan::with(['wilayah', 'subWilayah', 'sales']);

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

        if ($request->filled('kode_sales')) {
            $query->where('kode_sales', $request->kode_sales);
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
        $salesmen = \App\Models\User::where(fn($q) => $q->where('role', 'sales')->orWhere('is_kanvas', 1))->where('status', 1)->orderBy('name')->get();
        return view('master.pelanggan.index', compact('pelanggans', 'wilayahs', 'subWilayahs', 'salesmen'));
    }

    public function create()
    {
        $item = new Pelanggan();
        $wilayahs = Wilayah::all();
        $subWilayahs = SubWilayah::all();
        $salesmen = \App\Models\User::where(fn($q) => $q->where('role', 'sales')->orWhere('is_kanvas', 1))->where('status', 1)->orderBy('name')->get();
        return view('master.pelanggan.form', compact('item', 'wilayahs', 'subWilayahs', 'salesmen'));
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
            'jenis_pelanggan' => 'nullable|string|max:30',
            'kode_sales' => 'nullable|string|exists:users,nik'
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
        $salesmen = \App\Models\User::where(fn($q) => $q->where('role', 'sales')->orWhere('is_kanvas', 1))->where('status', 1)->orderBy('name')->get();
        return view('master.pelanggan.form', compact('item', 'wilayahs', 'subWilayahs', 'salesmen'));
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
            'jenis_pelanggan' => 'nullable|string|max:30',
            'kode_sales' => 'nullable|string|exists:users,nik'
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

    public function toggleJenis($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->jenis_pelanggan = $pelanggan->jenis_pelanggan == '1' ? '0' : '1';
        $pelanggan->save();

        return redirect()->back()->with('success', "Tipe pelanggan '{$pelanggan->nama_pelanggan}' berhasil diubah!");
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

        // Filter by canvas sales
        $canvasSalesNiks = \App\Models\User::where('is_kanvas', 1)->pluck('nik')->filter()->toArray();
        $allCanvasCustomerIds = \App\Models\User::whereNotNull('kode_pelanggan')->pluck('kode_pelanggan')->filter()->toArray();

        if (auth()->check()) {
            $currentUser = auth()->user();
            if ($currentUser->is_kanvas) {
                // Canvas Sales: can see all regular customers + only their own canvas dummy customer + shop customers assigned to them
                $query->where(function ($q) use ($currentUser, $canvasSalesNiks, $allCanvasCustomerIds) {
                    $q->where(function ($sub) use ($canvasSalesNiks, $allCanvasCustomerIds) {
                        if (!empty($canvasSalesNiks)) {
                            $sub->where(function ($inner) use ($canvasSalesNiks) {
                                $inner->whereNotIn('kode_sales', $canvasSalesNiks)
                                      ->orWhereNull('kode_sales');
                            });
                        }
                        if (!empty($allCanvasCustomerIds)) {
                            $sub->whereNotIn('kode_pelanggan', $allCanvasCustomerIds);
                        }
                    })
                    ->orWhere('kode_sales', $currentUser->nik);
                    
                    if ($currentUser->kode_pelanggan) {
                        $q->orWhere('kode_pelanggan', $currentUser->kode_pelanggan);
                    }
                });
            } else {
                // Regular Sales (role: sales/spv sales): cannot see any canvas dummy customers or shop customers assigned to canvas sales
                $role = strtolower($currentUser->role ?? '');
                if (in_array($role, ['sales', 'spv sales'])) {
                    if (!empty($canvasSalesNiks)) {
                        $query->where(function ($q) use ($canvasSalesNiks) {
                            $q->whereNotIn('kode_sales', $canvasSalesNiks)
                              ->orWhereNull('kode_sales');
                        });
                    }
                    if (!empty($allCanvasCustomerIds)) {
                        $query->whereNotIn('kode_pelanggan', $allCanvasCustomerIds);
                    }
                }
            }
        }

        if ($request->filled('kode_sales')) {
            $sales = \App\Models\User::where('nik', $request->kode_sales)->first();
            if ($sales && $sales->is_kanvas) {
                $query->where('kode_pelanggan', $sales->kode_pelanggan);
            }
        }

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
            ->limit(20)
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
                $invoices = $p->getOverdueInvoices($excludeNoFaktur)->load('sales');
                foreach ($invoices as $inv) {
                    $sisa = $inv->grand_total - $inv->getApprovedPembayaranTotal() - $inv->getTotalRetur();
                    $dueDate = \Carbon\Carbon::parse($inv->tanggal)->addDays($p->ljt ?? 30);
                    $overdueInvoices[] = [
                        'no_faktur' => $inv->no_faktur,
                        'tanggal' => \Carbon\Carbon::parse($inv->tanggal)->format('d/m/Y'),
                        'ljt' => $p->ljt ?? 30,
                        'due_date' => $dueDate->format('d/m/Y'),
                        'sales_name' => $inv->sales->name ?? $inv->kode_sales,
                        'sisa' => $sisa
                    ];
                }
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
                'jenis_pelanggan' => $p->jenis_pelanggan ?: '0',
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

    public function sisaLimitDetail($kode_pelanggan)
    {
        $pelanggan = Pelanggan::findOrFail($kode_pelanggan);

        $outstanding = $pelanggan->getOutstandingPiutang();
        $sisa        = $pelanggan->getSisaLimitKredit();

        // Ambil detail faktur yang masih punya sisa hutang
        $fakturBelumLunas = \App\Models\Penjualan::where('kode_pelanggan', $kode_pelanggan)
            ->where('batal', 0)
            ->select('no_faktur', 'tanggal', 'jenis_transaksi', 'grand_total')
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($f) {
                $bayarTunai = \DB::table('penjualan_pembayaran')
                    ->where('no_faktur', $f->no_faktur)->where('status', 'disetujui')->sum('jumlah');
                $bayarTf = \DB::table('penjualan_pembayaran_transfer')
                    ->where('no_faktur', $f->no_faktur)->where('status', 'disetujui')->sum('jumlah');
                $bayarGiro = \DB::table('penjualan_pembayaran_giro')
                    ->where('no_faktur', $f->no_faktur)->where('status', 'disetujui')->sum('jumlah');
                $retur = \DB::table('retur_penjualan')
                    ->where('no_faktur', $f->no_faktur)->sum('total');

                $totalBayar = $bayarTunai + $bayarTf + $bayarGiro + $retur;
                $sisaHutang = $f->grand_total - $totalBayar;

                if ($sisaHutang < 1) return null;

                return [
                    'no_faktur'      => $f->no_faktur,
                    'tanggal'        => \Carbon\Carbon::parse($f->tanggal)->format('d/m/Y'),
                    'jenis'          => $f->jenis_transaksi,
                    'grand_total'    => $f->grand_total,
                    'total_bayar'    => $totalBayar,
                    'sisa_hutang'    => $sisaHutang,
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'nama_pelanggan' => $pelanggan->nama_pelanggan,
            'kode_pelanggan' => $pelanggan->kode_pelanggan,
            'limit'          => $pelanggan->limit_pelanggan,
            'outstanding'    => $outstanding,
            'sisa_limit'     => $sisa,
            'faktur'         => $fakturBelumLunas,
        ]);
    }
}
