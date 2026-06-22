<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\PenjualanPembayaran;
use App\Models\Pelanggan;
use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\User;
use App\Models\DiskonStrata;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        $query = Penjualan::with(['pelanggan.wilayah', 'pembayarans', 'sales'])
            ->where('batal', 0);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('no_faktur', 'like', '%' . $request->search . '%')
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

        if ($request->filled('status_pembayaran')) {
            $status = $request->status_pembayaran;
            $query->where(function ($q) use ($status) {
                $paymentSql = "
                    (
                        COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran WHERE penjualan_pembayaran.no_faktur = penjualan.no_faktur AND status = 'disetujui' AND jenis_bayar != 'Retur'), 0) +
                        COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_transfer WHERE penjualan_pembayaran_transfer.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) +
                        COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_giro WHERE penjualan_pembayaran_giro.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0) +
                        COALESCE((SELECT SUM(total) FROM retur_penjualan WHERE retur_penjualan.no_faktur = penjualan.no_faktur), 0)
                    )
                ";
                if ($status === 'lunas') {
                    $q->whereRaw("grand_total - {$paymentSql} < 1");
                } else {
                    $q->whereRaw("grand_total - {$paymentSql} >= 1");
                }
            });
        }

        if ($request->filled('kode_sales')) {
            $query->where('kode_sales', $request->kode_sales);
        }

        if ($request->filled('kode_wilayah')) {
            $query->whereHas('pelanggan', function ($q) use ($request) {
                $q->where('kode_wilayah', $request->kode_wilayah);
            });
        }

        $salesmen = User::where(function ($q) {
            $q->where('role', 'sales')->orWhere('role', 'Salesman');
        })->where('status', '1')->orderBy('name')->get();
        $wilayahs = \App\Models\Wilayah::orderBy('nama_wilayah')->get();
        $items = $query->orderBy('tanggal', 'desc')->orderBy('no_faktur', 'desc')->paginate(15)->appends($request->query());
        return view('penjualan.index', compact('items', 'salesmen', 'wilayahs'));
    }

    public function create()
    {
        $item = new Penjualan();

        // Auto-generate no_faktur: XXXX-PJ-MJ-MMYY
        $todayDate = date('my'); // e.g. 0626 for June 2026
        $last = Penjualan::where('no_faktur', 'like', '%-PJ-MJ-' . $todayDate)
            ->orderBy('no_faktur', 'desc')
            ->first();

        $nextNumber = 1;
        if ($last) {
            $lastNum = intval(substr($last->no_faktur, 0, 4));
            $nextNumber = $lastNum + 1;
        }
        $item->no_faktur = str_pad($nextNumber, 4, '0', STR_PAD_LEFT) . '-PJ-MJ-' . $todayDate;

        $excludeNoFaktur = $item->no_faktur;
        $today = now()->toDateString();

        $outstandingSubquery = DB::table('penjualan')
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

        $overdueSubquery = DB::table('penjualan')
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

        $selectedKode = old('kode_pelanggan');
        if ($selectedKode) {
            $pelanggans = Pelanggan::where('kode_pelanggan', $selectedKode)
                ->select('pelanggan.*')
                ->addSelect([
                    'outstanding_piutang' => $outstandingSubquery,
                    'has_overdue' => $overdueSubquery->limit(1)
                ])
                ->get();
        } else {
            $pelanggans = collect();
        }

        $diskonStrata = DiskonStrata::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('berlaku_dari')->orWhere('berlaku_dari', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', now());
            })
            ->with(['details', 'barangs', 'kategori', 'merk'])
            ->get();

        return view('penjualan.form', compact('item', 'pelanggans', 'diskonStrata'));
    }

    public function store(Request $request)
    {
        $isKredit = in_array($request->jenis_transaksi, ['K', 'Kredit']);
        $request->merge([
            'jenis_transaksi' => $isKredit ? 'Kredit' : 'Tunai'
        ]);

        $request->validate([
            'no_faktur' => 'required|string|unique:penjualan,no_faktur',
            'tanggal' => 'required|date',
            'tanggal_kirim' => 'nullable|date',
            'kode_pelanggan' => 'required|string|exists:pelanggan,kode_pelanggan',
            'jenis_transaksi' => 'required|in:Tunai,Kredit',
            'diskon_global' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|exists:barang,kode_barang',
            'items.*.satuan_id' => 'required|integer',
            'items.*.satuan' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.harga' => 'required|numeric|min:0',
            'items.*.diskon' => 'required|numeric|min:0',
            'items.*.diskon1_persen' => 'nullable|numeric|min:0|max:100',
            'items.*.diskon2_persen' => 'nullable|numeric|min:0|max:100',
            'items.*.diskon3_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        // Calculate grand total and validate credit limit
        $tempSubtotalSum = 0;
        $tempTotalDiskon = 0;
        foreach ($request->items as $row) {
            $sub = $row['qty'] * $row['harga'];
            $d1 = $sub * (floatval($row['diskon1_persen'] ?? 0) / 100);
            $d2 = ($sub - $d1) * (floatval($row['diskon2_persen'] ?? 0) / 100);
            $d3 = ($sub - $d1 - $d2) * (floatval($row['diskon3_persen'] ?? 0) / 100);
            $tempSubtotalSum += $sub;
            $tempTotalDiskon += round($d1 + $d2 + $d3, 2);
        }
        $tempGrandTotal = $tempSubtotalSum - $tempTotalDiskon - ($request->diskon_global ?? 0);

        $pelanggan = Pelanggan::findOrFail($request->kode_pelanggan);
        if ($pelanggan->hasOverdueInvoices()) {
            return redirect()->back()->withInput()->with('error', "Gagal menyimpan transaksi. Pelanggan ini memiliki faktur yang sudah jatuh tempo!");
        }

        // Verify product restrictions for the salesman
        $user = auth()->user();
        if ($user && ($user->jenis_sales === 'kategori' || $user->jenis_sales === 'merk')) {
            $allowedItems = array_map('trim', explode(',', $user->jenis_barang ?? ''));
            foreach ($request->items as $row) {
                $barang = Barang::findOrFail($row['kode_barang']);
                if ($user->jenis_sales === 'kategori') {
                    if (!in_array($barang->kategori, $allowedItems)) {
                        return redirect()->back()->withInput()->with('error', "Gagal menyimpan transaksi. Barang '{$barang->nama_barang}' di luar kategori yang diizinkan untuk Anda!");
                    }
                } elseif ($user->jenis_sales === 'merk') {
                    if (!in_array($barang->merk, $allowedItems)) {
                        return redirect()->back()->withInput()->with('error', "Gagal menyimpan transaksi. Barang '{$barang->nama_barang}' di luar merk yang diizinkan untuk Anda!");
                    }
                }
            }
        }

        if ($request->jenis_transaksi === 'Kredit') {
            $sisaLimit = $pelanggan->getSisaLimitKredit();
            if ($tempGrandTotal > $sisaLimit) {
                return redirect()->back()->withInput()->with('error', "Gagal menyimpan transaksi. Batas limit kredit pelanggan terlampaui! Sisa limit kredit saat ini: Rp " . number_format($sisaLimit, 0, ',', '.'));
            }
        }

        try {
            DB::transaction(function () use ($request) {
                $subtotalSum = 0;
                $totalDiskon = 0;
                $details = [];

                foreach ($request->items as $row) {
                    // Decrement stock
                    $satuan = BarangSatuan::findOrFail($row['satuan_id']);
                    $qtySmallest = $row['qty'] * ($satuan->isi ?? 1);

                    // Validate stock level
                    $barang = Barang::lockForUpdate()->findOrFail($row['kode_barang']);
                    if ($barang->stok < $qtySmallest) {
                        throw new \Exception("Stok barang '{$barang->nama_barang}' tidak mencukupi! Sisa stok: " . $barang->formatStok($barang->stok));
                    }
                    $barang->decrement('stok', $qtySmallest);

                    $subtotal = $row['qty'] * $row['harga'];
                    $d1_pct = floatval($row['diskon1_persen'] ?? 0);
                    $d2_pct = floatval($row['diskon2_persen'] ?? 0);
                    $d3_pct = floatval($row['diskon3_persen'] ?? 0);

                    $d1 = $subtotal * ($d1_pct / 100);
                    $d2 = ($subtotal - $d1) * ($d2_pct / 100);
                    $d3 = ($subtotal - $d1 - $d2) * ($d3_pct / 100);

                    $rowDiskon = round($d1 + $d2 + $d3, 2);
                    $rowTotal = $subtotal - $rowDiskon;

                    $subtotalSum += $subtotal;
                    $totalDiskon += $rowDiskon;

                    $details[] = new PenjualanDetail([
                        'kode_barang' => $row['kode_barang'],
                        'satuan_id' => $row['satuan_id'],
                        'qty' => $row['qty'],
                        'harga' => $row['harga'],
                        'subtotal' => $subtotal,
                        'diskon1_persen' => $d1_pct,
                        'diskon2_persen' => $d2_pct,
                        'diskon3_persen' => $d3_pct,
                        'total_diskon' => $rowDiskon,
                        'total' => $rowTotal,
                        'harga_pokok' => $satuan->harga_pokok ?? 0,
                    ]);
                }

                $diskonGlobal = $request->diskon_global ?? 0;
                $grandTotal = $subtotalSum - $totalDiskon - $diskonGlobal;

                $penjualan = Penjualan::create([
                    'no_faktur' => $request->no_faktur,
                    'tanggal' => $request->tanggal,
                    'tanggal_kirim' => $request->tanggal_kirim,
                    'kode_pelanggan' => $request->kode_pelanggan,
                    'jenis_transaksi' => $isKredit ? 'K' : 'T',
                    'jenis_bayar' => $isKredit ? 'Kredit' : 'Tunai',
                    'total' => $subtotalSum,
                    'diskon' => $totalDiskon + $diskonGlobal,
                    'grand_total' => $grandTotal,
                    'keterangan' => $request->keterangan,
                    'id_user' => Auth::id() ?? 1,
                    'batal' => 0,
                ]);

                $penjualan->details()->saveMany($details);

                // Jika Tunai, langsung catat pembayaran lunas
                if (!$isKredit) {
                    $prefix = date('my');
                    $last = PenjualanPembayaran::where('no_bukti', 'like', $prefix . '%')->orderBy('no_bukti', 'desc')->first();
                    $nextNo = 1;
                    if ($last) {
                        $lastNum = intval(substr($last->no_bukti, 4));
                        $nextNo = $lastNum + 1;
                    }
                    $noBukti = $prefix . str_pad($nextNo, 4, '0', STR_PAD_LEFT);

                    PenjualanPembayaran::create([
                        'no_bukti' => $noBukti,
                        'tanggal' => $request->tanggal,
                        'no_faktur' => $penjualan->no_faktur,
                        'kode_pelanggan' => $penjualan->kode_pelanggan,
                        'jenis_bayar' => 'Cash',
                        'jumlah' => $grandTotal,
                        'keterangan' => 'Pembayaran Tunai Otomatis',
                        'id_user' => Auth::id() ?? 1,
                        'status' => 'pending',
                    ]);

                    ActivityLog::create([
                        'user_id' => Auth::id() ?? 1,
                        'action' => 'Input Pembayaran Tunai',
                        'description' => $noBukti . ' (Pending Approval)',
                        'ip_address' => $request->ip(),
                        'no_faktur' => $penjualan->no_faktur,
                    ]);
                }

                ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'Tambah Penjualan',
                    'description' => $penjualan->no_faktur . ' (Pelanggan: ' . $penjualan->kode_pelanggan . ')',
                    'ip_address' => $request->ip(),
                    'no_faktur' => $penjualan->no_faktur,
                ]);
            });
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('penjualan.index')->with('success', 'Transaksi penjualan berhasil disimpan.');
    }

    public function show($no_faktur)
    {
        $item = Penjualan::with(['pelanggan', 'details.barang', 'details.barangSatuan', 'pembayarans'])
            ->findOrFail($no_faktur);
        $allPembayarans = $item->getAllPembayarans();
        $totalBayar = $allPembayarans->where('status', 'disetujui')->sum('jumlah');
        $totalPending = $allPembayarans->where('status', 'pending')->sum('jumlah');

        // Fetch linked returns (Retur Potong Faktur / PF)
        $returs = \App\Models\ReturPenjualan::where('no_faktur', $no_faktur)->get();
        $totalRetur = $returs->sum('total');

        $sisaBayar = $item->getSisaPiutang();

        $salesmen = User::where(function ($q) {
            $q->where('role', 'sales')->orWhere('role', 'Salesman');
        })->where('status', '1')->orderBy('name')->get();

        return view('penjualan.show', compact('item', 'totalBayar', 'totalPending', 'sisaBayar', 'salesmen', 'allPembayarans', 'returs', 'totalRetur'));
    }

    public function print(Request $request, $no_faktur)
    {
        $item = Penjualan::with(['pelanggan.wilayah', 'details.barang', 'details.barangSatuan', 'user'])
            ->findOrFail($no_faktur);

        if ($item->cetak >= 1) {
            if (!$request->filled('alasan')) {
                return redirect()->back()->with('error', 'Faktur ini sudah pernah dicetak. Anda harus memberikan alasan untuk mencetak ulang.');
            }

            $alasan = $request->alasan;
            ActivityLog::create([
                'user_id' => Auth::id() ?? 1,
                'action' => 'Cetak Ulang Faktur',
                'description' => 'Cetak Ulang Faktur ' . $no_faktur . ' dengan alasan: ' . $alasan,
                'ip_address' => $request->ip(),
                'no_faktur' => $no_faktur,
            ]);
        } else {
            ActivityLog::create([
                'user_id' => Auth::id() ?? 1,
                'action' => 'Cetak Faktur',
                'description' => 'Cetak Pertama Faktur ' . $no_faktur,
                'ip_address' => $request->ip(),
                'no_faktur' => $no_faktur,
            ]);
        }

        // Increment print count
        $item->increment('cetak');

        return view('penjualan.print', compact('item'));
    }

    public function edit($no_faktur)
    {
        $item = Penjualan::with(['details.barang', 'details.barangSatuan'])->findOrFail($no_faktur);

        $excludeNoFaktur = $item->no_faktur;
        $today = now()->toDateString();

        $outstandingSubquery = DB::table('penjualan')
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

        $overdueSubquery = DB::table('penjualan')
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

        $selectedKode = old('kode_pelanggan', $item->kode_pelanggan);
        if ($selectedKode) {
            $pelanggans = Pelanggan::where('kode_pelanggan', $selectedKode)
                ->select('pelanggan.*')
                ->addSelect([
                    'outstanding_piutang' => $outstandingSubquery,
                    'has_overdue' => $overdueSubquery->limit(1)
                ])
                ->get();
        } else {
            $pelanggans = collect();
        }

        $diskonStrata = DiskonStrata::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('berlaku_dari')->orWhere('berlaku_dari', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', now());
            })
            ->with(['details', 'barangs', 'kategori', 'merk'])
            ->get();

        return view('penjualan.form', compact('item', 'pelanggans', 'diskonStrata'));
    }

    public function update(Request $request, $no_faktur)
    {
        $penjualan = Penjualan::with('details')->findOrFail($no_faktur);

        $isKredit = in_array($request->jenis_transaksi, ['K', 'Kredit']);
        $request->merge([
            'jenis_transaksi' => $isKredit ? 'Kredit' : 'Tunai'
        ]);

        $request->validate([
            'tanggal' => 'required|date',
            'tanggal_kirim' => 'nullable|date',
            'kode_pelanggan' => 'required|string|exists:pelanggan,kode_pelanggan',
            'jenis_transaksi' => 'required|in:Tunai,Kredit',
            'diskon_global' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|exists:barang,kode_barang',
            'items.*.satuan_id' => 'required|integer',
            'items.*.satuan' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.harga' => 'required|numeric|min:0',
            'items.*.diskon' => 'required|numeric|min:0',
            'items.*.diskon1_persen' => 'nullable|numeric|min:0|max:100',
            'items.*.diskon2_persen' => 'nullable|numeric|min:0|max:100',
            'items.*.diskon3_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        // Calculate grand total and validate credit limit
        $tempSubtotalSum = 0;
        $tempTotalDiskon = 0;
        foreach ($request->items as $row) {
            $sub = $row['qty'] * $row['harga'];
            $d1 = $sub * (floatval($row['diskon1_persen'] ?? 0) / 100);
            $d2 = ($sub - $d1) * (floatval($row['diskon2_persen'] ?? 0) / 100);
            $d3 = ($sub - $d1 - $d2) * (floatval($row['diskon3_persen'] ?? 0) / 100);
            $tempSubtotalSum += $sub;
            $tempTotalDiskon += round($d1 + $d2 + $d3, 2);
        }
        $tempGrandTotal = $tempSubtotalSum - $tempTotalDiskon - ($request->diskon_global ?? 0);

        $pelanggan = Pelanggan::findOrFail($request->kode_pelanggan);

        // Verify product restrictions for the salesman
        $user = auth()->user();
        if ($user && ($user->jenis_sales === 'kategori' || $user->jenis_sales === 'merk')) {
            $allowedItems = array_map('trim', explode(',', $user->jenis_barang ?? ''));
            foreach ($request->items as $row) {
                $barang = Barang::findOrFail($row['kode_barang']);
                if ($user->jenis_sales === 'kategori') {
                    if (!in_array($barang->kategori, $allowedItems)) {
                        return redirect()->back()->withInput()->with('error', "Gagal memperbarui transaksi. Barang '{$barang->nama_barang}' di luar kategori yang diizinkan untuk Anda!");
                    }
                } elseif ($user->jenis_sales === 'merk') {
                    if (!in_array($barang->merk, $allowedItems)) {
                        return redirect()->back()->withInput()->with('error', "Gagal memperbarui transaksi. Barang '{$barang->nama_barang}' di luar merk yang diizinkan untuk Anda!");
                    }
                }
            }
        }

        if ($isKredit) {
            $sisaLimit = $pelanggan->getSisaLimitKredit($no_faktur);
            if ($tempGrandTotal > $sisaLimit) {
                return redirect()->back()->withInput()->with('error', "Gagal memperbarui transaksi. Batas limit kredit pelanggan terlampaui! Sisa limit kredit saat ini: Rp " . number_format($sisaLimit, 0, ',', '.'));
            }
        }

        try {
            DB::transaction(function () use ($request, $penjualan) {
                // Revert old stock
                foreach ($penjualan->details as $oldDetail) {
                    $oldSatuan = BarangSatuan::find($oldDetail->satuan_id);
                    $oldQty = $oldDetail->qty * ($oldSatuan->isi ?? 1);
                    Barang::where('kode_barang', $oldDetail->kode_barang)->increment('stok', $oldQty);
                }

                $subtotalSum = 0;
                $totalDiskon = 0;
                $details = [];

                foreach ($request->items as $row) {
                    $satuan = BarangSatuan::findOrFail($row['satuan_id']);
                    $qtySmallest = $row['qty'] * ($satuan->isi ?? 1);

                    // Validate stock level
                    $barang = Barang::lockForUpdate()->findOrFail($row['kode_barang']);
                    if ($barang->stok < $qtySmallest) {
                        throw new \Exception("Stok barang '{$barang->nama_barang}' tidak mencukupi! Sisa stok: " . $barang->formatStok($barang->stok));
                    }
                    $barang->decrement('stok', $qtySmallest);

                    $subtotal = $row['qty'] * $row['harga'];
                    $d1_pct = floatval($row['diskon1_persen'] ?? 0);
                    $d2_pct = floatval($row['diskon2_persen'] ?? 0);
                    $d3_pct = floatval($row['diskon3_persen'] ?? 0);

                    $d1 = $subtotal * ($d1_pct / 100);
                    $d2 = ($subtotal - $d1) * ($d2_pct / 100);
                    $d3 = ($subtotal - $d1 - $d2) * ($d3_pct / 100);

                    $rowDiskon = round($d1 + $d2 + $d3, 2);
                    $rowTotal = $subtotal - $rowDiskon;

                    $subtotalSum += $subtotal;
                    $totalDiskon += $rowDiskon;

                    $details[] = new PenjualanDetail([
                        'kode_barang' => $row['kode_barang'],
                        'satuan_id' => $row['satuan_id'],
                        'qty' => $row['qty'],
                        'harga' => $row['harga'],
                        'subtotal' => $subtotal,
                        'diskon1_persen' => $d1_pct,
                        'diskon2_persen' => $d2_pct,
                        'diskon3_persen' => $d3_pct,
                        'total_diskon' => $rowDiskon,
                        'total' => $rowTotal,
                        'harga_pokok' => $satuan->harga_pokok ?? 0,
                    ]);
                }

                $diskonGlobal = $request->diskon_global ?? 0;
                $grandTotal = $subtotalSum - $totalDiskon - $diskonGlobal;

                $penjualan->update([
                    'tanggal' => $request->tanggal,
                    'tanggal_kirim' => $request->tanggal_kirim,
                    'kode_pelanggan' => $request->kode_pelanggan,
                    'jenis_transaksi' => $isKredit ? 'K' : 'T',
                    'jenis_bayar' => $isKredit ? 'Kredit' : 'Tunai',
                    'total' => $subtotalSum,
                    'diskon' => $totalDiskon + $diskonGlobal,
                    'grand_total' => $grandTotal,
                    'keterangan' => $request->keterangan,
                ]);

                $penjualan->details()->delete();
                $penjualan->details()->saveMany($details);

                ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'Edit Penjualan',
                    'description' => $penjualan->no_faktur,
                    'ip_address' => $request->ip(),
                    'no_faktur' => $penjualan->no_faktur,
                ]);
            });
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('penjualan.index')->with('success', 'Transaksi penjualan berhasil diperbarui.');
    }

    public function destroy($no_faktur)
    {
        $penjualan = Penjualan::with('details')->findOrFail($no_faktur);

        DB::transaction(function () use ($penjualan) {
            // Revert stock
            foreach ($penjualan->details as $detail) {
                $satuan = BarangSatuan::find($detail->satuan_id);
                $qty = $detail->qty * ($satuan->isi ?? 1);
                Barang::where('kode_barang', $detail->kode_barang)->increment('stok', $qty);
            }
            $penjualan->delete();

            ActivityLog::create([
                'user_id' => Auth::id() ?? 1,
                'action' => 'Hapus Penjualan',
                'description' => $penjualan->no_faktur,
                'ip_address' => request()->ip(),
                'no_faktur' => $penjualan->no_faktur,
            ]);
        });

        return redirect()->route('penjualan.index')->with('success', 'Transaksi penjualan berhasil dihapus.');
    }

    public function batal(Request $request, $no_faktur)
    {
        $request->validate([
            'alasan_batal' => 'required|string|max:255',
        ]);

        $penjualan = Penjualan::with(['details', 'pembayarans'])->findOrFail($no_faktur);

        if ($penjualan->batal == 1) {
            return redirect()->back()->with('error', 'Transaksi ini sudah dibatalkan sebelumnya.');
        }

        try {
            DB::transaction(function () use ($penjualan, $request) {
                // Revert stock
                foreach ($penjualan->details as $detail) {
                    $satuan = BarangSatuan::find($detail->satuan_id);
                    $qty = $detail->qty * ($satuan->isi ?? 1);
                    Barang::where('kode_barang', $detail->kode_barang)->increment('stok', $qty);
                }

                // Update penjualan status to canceled
                $penjualan->update([
                    'batal' => 1,
                    'alasan_batal' => $request->alasan_batal,
                ]);

                // Update status of all associated payments to ditolak
                DB::table('penjualan_pembayaran')->where('no_faktur', $penjualan->no_faktur)->update([
                    'status' => 'ditolak',
                    'keterangan' => DB::raw("CONCAT(COALESCE(keterangan, ''), ' (Faktur Dibatalkan)')")
                ]);

                DB::table('penjualan_pembayaran_transfer')->where('no_faktur', $penjualan->no_faktur)->update([
                    'status' => 'ditolak',
                    'keterangan' => DB::raw("CONCAT(COALESCE(keterangan, ''), ' (Faktur Dibatalkan)')")
                ]);

                DB::table('penjualan_pembayaran_giro')->where('no_faktur', $penjualan->no_faktur)->update([
                    'status' => 'ditolak',
                    'keterangan' => DB::raw("CONCAT(COALESCE(keterangan, ''), ' (Faktur Dibatalkan)')")
                ]);

                ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'Batal Penjualan',
                    'description' => 'Membatalkan faktur ' . $penjualan->no_faktur . ' dengan alasan: ' . $request->alasan_batal,
                    'ip_address' => $request->ip(),
                    'no_faktur' => $penjualan->no_faktur,
                ]);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }

        return redirect()->route('penjualan.index')->with('success', 'Transaksi penjualan berhasil dibatalkan.');
    }

    public function storePayment(Request $request, $no_faktur)
    {
        $item = Penjualan::findOrFail($no_faktur);

        $request->validate([
            'no_bukti' => 'nullable|string|max:50',
            'tanggal' => 'required|date',
            'jenis_bayar' => 'required|string|max:50',
            'jumlah' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string',
            'kode_sales' => 'required|string|exists:users,nik',
        ]);

        $totalBayarApproved = $item->pembayarans->where('status', 'disetujui')->sum('jumlah');
        $totalBayarPending = $item->pembayarans->where('status', 'pending')->sum('jumlah');
        $totalRetur = $item->getTotalRetur();
        $sisaBayarValidation = $item->grand_total - ($totalBayarApproved + $totalBayarPending) - $totalRetur;
        if ($sisaBayarValidation < 1) {
            $sisaBayarValidation = 0.0;
        }

        if ($request->jumlah > $sisaBayarValidation) {
            return redirect()->back()->with('error', 'Jumlah pembayaran melebihi sisa piutang! Sisa saat ini (termasuk pending): Rp ' . number_format($sisaBayarValidation, 0, ',', '.'));
        }

        // Auto-generate no_bukti
        $no_bukti = $request->no_bukti;
        if (empty($no_bukti)) {
            $prefix = date('my');
            $last = PenjualanPembayaran::where('no_bukti', 'like', $prefix . '%')->orderBy('no_bukti', 'desc')->first();
            $nextNo = 1;
            if ($last) {
                $lastNum = intval(substr($last->no_bukti, 4));
                $nextNo = $lastNum + 1;
            }
            $no_bukti = $prefix . str_pad($nextNo, 4, '0', STR_PAD_LEFT);
        }

        // All payment types (Cash, Transfer, Giro, etc.) start with pending status
        $status = 'pending';

        PenjualanPembayaran::create([
            'no_bukti' => $no_bukti,
            'tanggal' => $request->tanggal,
            'no_faktur' => $no_faktur,
            'kode_pelanggan' => $item->kode_pelanggan,
            'kode_sales' => $request->kode_sales,
            'jenis_bayar' => $request->jenis_bayar,
            'jumlah' => $request->jumlah,
            'keterangan' => $request->keterangan,
            'id_user' => Auth::id() ?? 1,
            'status' => $status,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id() ?? 1,
            'action' => 'Input Pembayaran ' . $request->jenis_bayar,
            'description' => $no_bukti . ' (Pending Approval)',
            'ip_address' => $request->ip(),
            'no_faktur' => $no_faktur,
        ]);

        $message = 'Pembayaran berhasil dicatat dan sedang menunggu persetujuan keuangan.';

        return redirect()->route('penjualan.show', $no_faktur)->with('success', $message);
    }

    public function pendingPayments(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $cashQuery = DB::table('penjualan_pembayaran')
            ->select([
                'id as id',
                'no_bukti as no_bukti',
                'tanggal',
                'no_faktur',
                'kode_pelanggan',
                'kode_sales',
                'jenis_bayar',
                'jumlah',
                'keterangan',
                'id_user',
                'status',
                'created_at',
                'updated_at',
                DB::raw("'cash' as source_table")
            ]);

        $transferQuery = DB::table('penjualan_pembayaran_transfer')
            ->select([
                'kode_transfer as id',
                'kode_transfer as no_bukti',
                'tanggal',
                'no_faktur',
                'kode_pelanggan',
                'kode_sales',
                DB::raw("COALESCE(jenis_bayar, 'Transfer') as jenis_bayar"),
                'jumlah',
                'keterangan',
                'id_user',
                'status',
                'created_at',
                'updated_at',
                DB::raw("'transfer' as source_table")
            ]);

        $giroQuery = DB::table('penjualan_pembayaran_giro')
            ->select([
                'kode_giro as id',
                'kode_giro as no_bukti',
                'tanggal',
                'no_faktur',
                'kode_pelanggan',
                'kode_sales',
                DB::raw("COALESCE(jenis_bayar, 'Giro') as jenis_bayar"),
                'jumlah',
                'keterangan',
                'id_user',
                'status',
                'created_at',
                'updated_at',
                DB::raw("'giro' as source_table")
            ]);

        $unionQuery = $cashQuery->unionAll($transferQuery)->unionAll($giroQuery);

        $query = DB::table(DB::raw("({$unionQuery->toSql()}) as p"))
            ->mergeBindings($unionQuery);

        if ($tab === 'history') {
            $query->whereIn('status', ['disetujui', 'ditolak'])
                ->orderBy('updated_at', 'desc');
        } else {
            $query->where('status', 'pending')
                ->orderBy('tanggal', 'desc');
        }

        // Apply jenis_bayar filter
        if ($request->filled('jenis_bayar')) {
            $jb = $request->jenis_bayar;
            if (strtolower($jb) === 'tunai') {
                $query->whereIn('jenis_bayar', ['tunai', 'Cash']);
            } elseif (strtolower($jb) === 'transfer') {
                $query->whereIn('jenis_bayar', ['transfer', 'Transfer']);
            } elseif (strtolower($jb) === 'giro') {
                $query->whereIn('jenis_bayar', ['giro', 'Giro']);
            } else {
                $query->where('jenis_bayar', $jb);
            }
        }

        // Apply date range filters
        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal', '<=', $request->tanggal_akhir);
        }

        $payments = $query->paginate(15)->appends($request->query());

        // Eager load relations manually
        $kodePelanggans = collect($payments->items())->pluck('kode_pelanggan')->filter()->unique()->toArray();
        $noFakturs = collect($payments->items())->pluck('no_faktur')->filter()->unique()->toArray();

        $pelanggans = Pelanggan::whereIn('kode_pelanggan', $kodePelanggans)->get()->keyBy('kode_pelanggan');
        $penjualans = Penjualan::with('sales')->whereIn('no_faktur', $noFakturs)->get()->keyBy('no_faktur');

        foreach ($payments as $payment) {
            $payment->pelanggan = $pelanggans->get($payment->kode_pelanggan);
            $payment->penjualan = $penjualans->get($payment->no_faktur);
        }

        $pendingPembayaranCount = DB::table('penjualan_pembayaran')->where('status', 'pending')->count()
            + DB::table('penjualan_pembayaran_transfer')->where('status', 'pending')->count()
            + DB::table('penjualan_pembayaran_giro')->where('status', 'pending')->count();

        return view('penjualan.pembayaran_pending', compact('payments', 'tab', 'pendingPembayaranCount'));
    }

    public function approvePayment(Request $request, $id)
    {
        $source = $request->query('source');
        $no_bukti = $id;
        $no_faktur = '';

        if ($source === 'transfer') {
            DB::table('penjualan_pembayaran_transfer')->where('kode_transfer', $id)->update(['status' => 'disetujui']);
            $payment = DB::table('penjualan_pembayaran_transfer')->where('kode_transfer', $id)->first();
            if ($payment) {
                $no_faktur = $payment->no_faktur;
                $no_bukti = $payment->kode_transfer;
            }
        } elseif ($source === 'giro') {
            DB::table('penjualan_pembayaran_giro')->where('kode_giro', $id)->update(['status' => 'disetujui']);
            $payment = DB::table('penjualan_pembayaran_giro')->where('kode_giro', $id)->first();
            if ($payment) {
                $no_faktur = $payment->no_faktur;
                $no_bukti = $payment->kode_giro;
            }
        } else {
            $payment = PenjualanPembayaran::findOrFail($id);
            $payment->update(['status' => 'disetujui']);
            $no_faktur = $payment->no_faktur;
            $no_bukti = $payment->no_bukti;
        }

        ActivityLog::create([
            'user_id' => Auth::id() ?? 1,
            'action' => 'Approve Pembayaran',
            'description' => 'Menyetujui Pembayaran ' . $no_bukti . ' untuk Faktur ' . $no_faktur,
            'ip_address' => request()->ip(),
            'no_faktur' => $no_faktur,
        ]);

        return redirect()->back()->with('success', 'Pembayaran berhasil disetujui.');
    }

    public function rejectPayment(Request $request, $id)
    {
        $source = $request->query('source');
        $no_bukti = $id;
        $no_faktur = '';

        if ($source === 'transfer') {
            DB::table('penjualan_pembayaran_transfer')->where('kode_transfer', $id)->update(['status' => 'ditolak']);
            $payment = DB::table('penjualan_pembayaran_transfer')->where('kode_transfer', $id)->first();
            if ($payment) {
                $no_faktur = $payment->no_faktur;
                $no_bukti = $payment->kode_transfer;
            }
        } elseif ($source === 'giro') {
            DB::table('penjualan_pembayaran_giro')->where('kode_giro', $id)->update(['status' => 'ditolak']);
            $payment = DB::table('penjualan_pembayaran_giro')->where('kode_giro', $id)->first();
            if ($payment) {
                $no_faktur = $payment->no_faktur;
                $no_bukti = $payment->kode_giro;
            }
        } else {
            $payment = PenjualanPembayaran::findOrFail($id);
            $payment->update(['status' => 'ditolak']);
            $no_faktur = $payment->no_faktur;
            $no_bukti = $payment->no_bukti;
        }

        ActivityLog::create([
            'user_id' => Auth::id() ?? 1,
            'action' => 'Reject Pembayaran',
            'description' => 'Menolak Pembayaran ' . $no_bukti . ' untuk Faktur ' . $no_faktur,
            'ip_address' => request()->ip(),
            'no_faktur' => $no_faktur,
        ]);

        return redirect()->back()->with('success', 'Pembayaran berhasil ditolak.');
    }

    public function cancelPaymentApproval(Request $request, $id)
    {
        $source = $request->query('source');
        $no_bukti = $id;
        $no_faktur = '';
        $oldStatus = 'unknown';

        if ($source === 'transfer') {
            $payment = DB::table('penjualan_pembayaran_transfer')->where('kode_transfer', $id)->first();
            if ($payment) {
                $oldStatus = $payment->status;
                $no_faktur = $payment->no_faktur;
                $no_bukti = $payment->kode_transfer;
                DB::table('penjualan_pembayaran_transfer')->where('kode_transfer', $id)->update(['status' => 'pending']);
            }
        } elseif ($source === 'giro') {
            $payment = DB::table('penjualan_pembayaran_giro')->where('kode_giro', $id)->first();
            if ($payment) {
                $oldStatus = $payment->status;
                $no_faktur = $payment->no_faktur;
                $no_bukti = $payment->kode_giro;
                DB::table('penjualan_pembayaran_giro')->where('kode_giro', $id)->update(['status' => 'pending']);
            }
        } else {
            $payment = PenjualanPembayaran::findOrFail($id);
            $oldStatus = $payment->status;
            $payment->update(['status' => 'pending']);
            $no_faktur = $payment->no_faktur;
            $no_bukti = $payment->no_bukti;
        }

        ActivityLog::create([
            'user_id' => Auth::id() ?? 1,
            'action' => 'Batal Approve Pembayaran',
            'description' => 'Membatalkan ' . ($oldStatus === 'disetujui' ? 'Persetujuan' : 'Penolakan') . ' Pembayaran ' . $no_bukti . ' untuk Faktur ' . $no_faktur,
            'ip_address' => request()->ip(),
            'no_faktur' => $no_faktur,
        ]);

        return redirect()->back()->with('success', 'Persetujuan/penolakan pembayaran berhasil dibatalkan dan status kembali menjadi pending.');
    }
}
