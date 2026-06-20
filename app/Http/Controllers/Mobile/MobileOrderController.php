<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\PenjualanPembayaran;
use App\Models\Pelanggan;
use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\PenjualanCheckin;
use App\Models\DiskonStrata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MobileOrderController extends Controller
{
    public function index(Request $request)
    {
        $nik = Auth::user()->nik;
        $q = $request->input('q');

        $query = Penjualan::with(['pelanggan', 'details.barang', 'details.barangSatuan', 'pembayarans'])
            ->where('kode_sales', $nik);

        if ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('no_faktur', 'like', "%{$q}%")
                    ->orWhereHas('pelanggan', function($custQuery) use ($q) {
                        $custQuery->where('nama_pelanggan', 'like', "%{$q}%")
                                  ->orWhere('kode_pelanggan', 'like', "%{$q}%");
                    });
            });
        }

        $orders = $query->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('mobile.history', compact('orders', 'q'));
    }

    public function create(Request $request)
    {
        $nik = Auth::user()->nik;

        // Enforce active check-in
        $activeCheckin = PenjualanCheckin::where('kode_sales', $nik)
            ->whereNull('checkout')
            ->first();

        if (!$activeCheckin) {
            return redirect()->route('mobile.kunjungan.index')->with('error', 'Anda harus melakukan Check-in di toko pelanggan terlebih dahulu sebelum membuat order!');
        }

        $selectedKode = $activeCheckin->kode_pelanggan;

        // Auto-generate no_faktur: XXXX-PJ-MJ-MMYY
        $todayDate = date('my'); // e.g. 0626 for June 2026
        $last  = Penjualan::where('no_faktur', 'like', '%-PJ-MJ-' . $todayDate)
            ->orderBy('no_faktur', 'desc')
            ->first();

        $nextNumber = 1;
        if ($last) {
            $lastNum = intval(substr($last->no_faktur, 0, 4));
            $nextNumber = $lastNum + 1;
        }
        $noFaktur = str_pad($nextNumber, 4, '0', STR_PAD_LEFT) . '-PJ-MJ-' . $todayDate;

        $pelanggan = null;
        if ($selectedKode) {
            $today = now()->toDateString();
            
            $outstandingSubquery = DB::table('penjualan')
                ->selectRaw("COALESCE(SUM(grand_total - COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran WHERE penjualan_pembayaran.no_faktur = penjualan.no_faktur AND status = 'disetujui'), 0)), 0)")
                ->whereColumn('penjualan.kode_pelanggan', 'pelanggan.kode_pelanggan')
                ->where('penjualan.batal', 0);

            $overdueSubquery = DB::table('penjualan')
                ->selectRaw('1')
                ->whereColumn('penjualan.kode_pelanggan', 'pelanggan.kode_pelanggan')
                ->whereIn('penjualan.jenis_transaksi', ['K', 'Kredit'])
                ->where('penjualan.batal', 0)
                ->whereRaw('DATE_ADD(penjualan.tanggal, INTERVAL COALESCE(pelanggan.ljt, 14) DAY) < ?', [$today])
                ->whereRaw("(SELECT COALESCE(SUM(jumlah), 0) FROM penjualan_pembayaran WHERE penjualan_pembayaran.no_faktur = penjualan.no_faktur AND status = 'disetujui') < penjualan.grand_total");

            $pelanggan = Pelanggan::with(['wilayah', 'subWilayah'])
                ->where('kode_pelanggan', $selectedKode)
                ->select('pelanggan.*')
                ->addSelect([
                    'outstanding_piutang' => $outstandingSubquery,
                    'has_overdue' => $overdueSubquery->limit(1)
                ])
                ->first();
        }

        // Fetch active diskon strata rules
        $diskonStrata = DiskonStrata::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('berlaku_dari')->orWhere('berlaku_dari', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', now());
            })
            ->with(['details', 'barangs', 'kategori', 'merk'])
            ->get();

        return view('mobile.order', compact('noFaktur', 'pelanggan', 'diskonStrata'));
    }

    public function store(Request $request)
    {
        $nik = Auth::user()->nik;

        // Enforce active check-in on store
        $activeCheckin = PenjualanCheckin::where('kode_sales', $nik)
            ->whereNull('checkout')
            ->first();

        if (!$activeCheckin) {
            return redirect()->route('mobile.kunjungan.index')->with('error', 'Anda harus melakukan Check-in di toko pelanggan terlebih dahulu!');
        }

        if ($activeCheckin->kode_pelanggan !== $request->kode_pelanggan) {
            return redirect()->route('mobile.kunjungan.index')->with('error', 'Toko check-in aktif tidak cocok dengan tujuan order!');
        }

        $request->validate([
            'no_faktur'         => 'required|string|unique:penjualan,no_faktur',
            'tanggal'           => 'required|date',
            'tanggal_kirim'     => 'nullable|date',
            'kode_pelanggan'    => 'required|string|exists:pelanggan,kode_pelanggan',
            'jenis_transaksi'   => 'required|in:Tunai,Kredit',
            'diskon_global'     => 'nullable|numeric|min:0',
            'keterangan'        => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.kode_barang' => 'required|exists:barang,kode_barang',
            'items.*.satuan_id'   => 'required|integer',
            'items.*.satuan'      => 'required|string',
            'items.*.qty'         => 'required|numeric|min:0.01',
            'items.*.harga'       => 'required|numeric|min:0',
            'items.*.diskon1_persen' => 'nullable|numeric|min:0|max:100',
            'items.*.diskon2_persen' => 'nullable|numeric|min:0|max:100',
            'items.*.diskon3_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        $pelanggan = Pelanggan::findOrFail($request->kode_pelanggan);

        // 1. Verify Overdue Invoices
        if ($pelanggan->hasOverdueInvoices()) {
            return redirect()->back()->withInput()->with('error', "Transaksi ditolak. Pelanggan memiliki faktur overdue!");
        }

        // Verify product restrictions for the salesman
        $user = auth()->user();
        if ($user && ($user->jenis_sales === 'kategori' || $user->jenis_sales === 'merk')) {
            $allowedItems = array_map('trim', explode(',', $user->jenis_barang ?? ''));
            foreach ($request->items as $row) {
                $barang = Barang::findOrFail($row['kode_barang']);
                if ($user->jenis_sales === 'kategori') {
                    if (!in_array($barang->kategori, $allowedItems)) {
                        return redirect()->back()->withInput()->with('error', "Transaksi ditolak. Barang '{$barang->nama_barang}' di luar kategori yang diizinkan untuk Anda!");
                    }
                } elseif ($user->jenis_sales === 'merk') {
                    if (!in_array($barang->merk, $allowedItems)) {
                        return redirect()->back()->withInput()->with('error', "Transaksi ditolak. Barang '{$barang->nama_barang}' di luar merk yang diizinkan untuk Anda!");
                    }
                }
            }
        }

        // Calculate Grand Total for validation
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

        // 2. Verify Credit Limit
        if ($request->jenis_transaksi === 'Kredit') {
            $sisaLimit = $pelanggan->getSisaLimitKredit();
            if ($tempGrandTotal > $sisaLimit) {
                return redirect()->back()->withInput()->with('error', "Limit kredit terlampaui! Sisa limit: Rp " . number_format($sisaLimit, 0, ',', '.'));
            }
        }

        // 3. Process Transaction
        try {
            DB::transaction(function () use ($request, $tempGrandTotal, $pelanggan) {
                $subtotalSum  = 0;
                $totalDiskon  = 0;
                $details      = [];

                foreach ($request->items as $row) {
                    // Decrement Stock based on smallest capacity unit
                    $satuan = BarangSatuan::findOrFail($row['satuan_id']);
                    $qtySmallest = $row['qty'] * ($satuan->isi ?? 1);

                    // Validate stock level
                    $barang = Barang::lockForUpdate()->findOrFail($row['kode_barang']);
                    if ($barang->stok < $qtySmallest) {
                        throw new \Exception("Stok barang '{$barang->nama_barang}' tidak mencukupi! Sisa stok: " . $barang->formatStok($barang->stok));
                    }
                    $barang->decrement('stok', $qtySmallest);

                    $subtotal    = $row['qty'] * $row['harga'];
                    $d1_pct      = floatval($row['diskon1_persen'] ?? 0);
                    $d2_pct      = floatval($row['diskon2_persen'] ?? 0);
                    $d3_pct      = floatval($row['diskon3_persen'] ?? 0);

                    $d1          = $subtotal * ($d1_pct / 100);
                    $d2          = ($subtotal - $d1) * ($d2_pct / 100);
                    $d3          = ($subtotal - $d1 - $d2) * ($d3_pct / 100);

                    $rowDiskon   = round($d1 + $d2 + $d3, 2);
                    $rowTotal    = $subtotal - $rowDiskon;

                    $subtotalSum += $subtotal;
                    $totalDiskon += $rowDiskon;

                    $details[] = new PenjualanDetail([
                        'kode_barang'    => $row['kode_barang'],
                        'satuan_id'      => $row['satuan_id'],
                        'qty'            => $row['qty'],
                        'harga'          => $row['harga'],
                        'subtotal'       => $subtotal,
                        'diskon1_persen' => $d1_pct,
                        'diskon2_persen' => $d2_pct,
                        'diskon3_persen' => $d3_pct,
                        'total_diskon'   => $rowDiskon,
                        'total'          => $rowTotal,
                        'harga_pokok'    => $satuan->harga_pokok ?? 0,
                    ]);
                }

                $diskonGlobal = $request->diskon_global ?? 0;
                $grandTotal   = $subtotalSum - $totalDiskon - $diskonGlobal;

                // Save Penjualan (Order)
                $penjualan = Penjualan::create([
                    'no_faktur'       => $request->no_faktur,
                    'tanggal'         => $request->tanggal,
                    'tanggal_kirim'   => $request->tanggal_kirim,
                    'kode_pelanggan'  => $request->kode_pelanggan,
                    'kode_sales'      => Auth::user()->nik, // Sales NIK
                    'jenis_transaksi' => $request->jenis_transaksi,
                    'jenis_bayar'     => $request->jenis_transaksi === 'Tunai' ? 'Tunai' : 'Kredit',
                    'total'           => $subtotalSum,
                    'diskon'          => $totalDiskon + $diskonGlobal,
                    'grand_total'     => $grandTotal,
                    'keterangan'      => $request->keterangan ?? 'Order via Mobile',
                    'id_user'         => Auth::id() ?? 1,
                    'batal'           => 0,
                ]);

                $penjualan->details()->saveMany($details);

                // Note: Pembayaran TIDAK dilakukan otomatis.
                // Semua transaksi (Tunai maupun Kredit) akan berstatus BELUM LUNAS.
                // Pembayaran hanya bisa diinput oleh admin/kasir melalui desktop.

                // Create visit checkout automatically if checkout parameter is true
                // but the visit log checkin is optional, so we log normal order activity
                \App\Models\ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'Tambah Penjualan (Mobile)',
                    'description' => $penjualan->no_faktur . ' (Pelanggan: ' . $penjualan->kode_pelanggan . ')',
                    'ip_address' => $request->ip(),
                    'no_faktur' => $penjualan->no_faktur,
                ]);
            });
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('mobile.kunjungan.index')->with('success', 'Pesanan penjualan ' . $request->no_faktur . ' berhasil disimpan.');
    }

    public function storePayment(Request $request, $no_faktur)
    {
        // Sales tidak diizinkan melakukan input pembayaran.
        // Pembayaran hanya dilakukan oleh admin/kasir melalui sistem desktop.
        abort(403, 'Anda tidak memiliki izin untuk melakukan input pembayaran.');
        $item = Penjualan::with('pembayarans')->findOrFail($no_faktur);

        $request->validate([
            'tanggal'     => 'required|date',
            'jenis_bayar' => 'required|string|max:50',
            'jumlah'      => 'required|numeric|min:0.01',
            'keterangan'  => 'nullable|string',
        ], [
            'tanggal.required'     => 'Tanggal wajib diisi.',
            'jenis_bayar.required' => 'Metode bayar wajib dipilih.',
            'jumlah.required'      => 'Jumlah pembayaran wajib diisi.',
            'jumlah.numeric'       => 'Jumlah pembayaran harus berupa angka.',
            'jumlah.min'           => 'Jumlah pembayaran harus lebih dari 0.',
        ]);

        // Enforce active check-in in the customer's store
        $nik = Auth::user()->nik;
        $activeCheckin = PenjualanCheckin::where('kode_sales', $nik)
            ->whereNull('checkout')
            ->first();

        if (!$activeCheckin || $activeCheckin->kode_pelanggan !== $item->kode_pelanggan) {
            return redirect()->back()->with('error', 'Anda harus melakukan Check-in di toko pelanggan ini terlebih dahulu sebelum mencatat pembayaran!');
        }

        $cash = $item->pembayarans->sum('jumlah');
        $transfer = DB::table('penjualan_pembayaran_transfer')->where('no_faktur', $no_faktur)->sum('jumlah');
        $giro = DB::table('penjualan_pembayaran_giro')->where('no_faktur', $no_faktur)->sum('jumlah');
        $totalBayar = $cash + $transfer + $giro;
        $sisaBayar  = $item->grand_total - $totalBayar;

        if ($request->jumlah > $sisaBayar) {
            return redirect()->back()->with('error', 'Jumlah pembayaran melebihi sisa piutang! Sisa saat ini: Rp ' . number_format($sisaBayar, 0, ',', '.'));
        }

        $jenis_bayar = $request->jenis_bayar;
        $no_bukti = '';

        if ($jenis_bayar === 'Transfer') {
            $todayPrefix = date('ym');
            $last = DB::table('penjualan_pembayaran_transfer')->where('kode_transfer', 'like', 'TF' . $todayPrefix . '%')->orderBy('kode_transfer', 'desc')->first();
            $nextNo = $last ? (intval(substr($last->kode_transfer, -4)) + 1) : 1;
            $no_bukti = 'TF' . $todayPrefix . str_pad($nextNo, 4, '0', STR_PAD_LEFT);

            DB::table('penjualan_pembayaran_transfer')->insert([
                'kode_transfer'  => $no_bukti,
                'no_faktur'      => $no_faktur,
                'kode_pelanggan' => $item->kode_pelanggan,
                'kode_sales'     => $nik,
                'jenis_bayar'    => $jenis_bayar,
                'jumlah'         => $request->jumlah,
                'tanggal'        => $request->tanggal,
                'status'         => 'pending',
                'keterangan'     => $request->keterangan,
                'id_user'        => Auth::id() ?? 1,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        } elseif ($jenis_bayar === 'Giro') {
            $todayPrefix = date('ym');
            $last = DB::table('penjualan_pembayaran_giro')->where('kode_giro', 'like', 'GR' . $todayPrefix . '%')->orderBy('kode_giro', 'desc')->first();
            $nextNo = $last ? (intval(substr($last->kode_giro, -4)) + 1) : 1;
            $no_bukti = 'GR' . $todayPrefix . str_pad($nextNo, 4, '0', STR_PAD_LEFT);

            DB::table('penjualan_pembayaran_giro')->insert([
                'kode_giro'      => $no_bukti,
                'no_faktur'      => $no_faktur,
                'kode_pelanggan' => $item->kode_pelanggan,
                'kode_sales'     => $nik,
                'jumlah'         => $request->jumlah,
                'tanggal'        => $request->tanggal,
                'status'         => 'pending',
                'keterangan'     => $request->keterangan,
                'jenis_bayar'    => $jenis_bayar,
                'id_user'        => Auth::id() ?? 1,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        } else {
            // Tunai / Default Cash table
            $today = date('Ymd');
            $last  = PenjualanPembayaran::where('no_bukti', 'like', 'BKK-PENJ-' . $today . '%')->orderBy('id', 'desc')->first();
            $nextNo = $last ? (intval(substr($last->no_bukti, -4)) + 1) : 1;
            $no_bukti = 'BKK-PENJ-' . $today . '-' . str_pad($nextNo, 4, '0', STR_PAD_LEFT);

            PenjualanPembayaran::create([
                'no_bukti'       => $no_bukti,
                'tanggal'        => $request->tanggal,
                'no_faktur'      => $no_faktur,
                'kode_pelanggan' => $item->kode_pelanggan,
                'kode_sales'     => $nik,
                'jenis_bayar'    => $jenis_bayar,
                'jumlah'         => $request->jumlah,
                'keterangan'     => $request->keterangan,
                'id_user'        => Auth::id() ?? 1,
                'status'         => 'pending',
            ]);
        }

        \App\Models\ActivityLog::create([
            'user_id'     => Auth::id() ?? 1,
            'action'      => 'Input Pembayaran ' . $jenis_bayar . ' (Mobile)',
            'description' => $no_bukti . ' (Pending Approval)',
            'ip_address'  => $request->ip(),
            'no_faktur'   => $no_faktur,
        ]);

        return redirect()->route('mobile.order.index')->with('success', 'Pembayaran sebesar Rp ' . number_format($request->jumlah, 0, ',', '.') . ' berhasil dicatat dan menunggu persetujuan keuangan.');
    }
}
