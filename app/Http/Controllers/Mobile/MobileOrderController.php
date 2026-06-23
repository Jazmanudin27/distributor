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
        $filter = $request->input('filter', 'all');
        $isSpv = strtolower(Auth::user()->role ?? '') === 'spv sales';

        $query = Penjualan::with(['pelanggan', 'details.barang', 'details.barangSatuan', 'pembayarans']);

        if (!$isSpv) {
            $query->where('kode_sales', $nik);
        }

        if ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('no_faktur', 'like', "%{$q}%")
                    ->orWhereHas('pelanggan', function($custQuery) use ($q) {
                        $custQuery->where('nama_pelanggan', 'like', "%{$q}%")
                                  ->orWhere('kode_pelanggan', 'like', "%{$q}%");
                    });
            });
        }

        // Apply filters
        $todayStr = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        if ($filter === 'today') {
            $query->whereDate('tanggal', $todayStr);
        } elseif ($filter === 'month') {
            $query->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);
        }

        // Calculate summary for today & month
        $todaySalesQuery = Penjualan::where('batal', 0)
            ->whereDate('tanggal', $todayStr);

        $monthSalesQuery = Penjualan::where('batal', 0)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);

        if (!$isSpv) {
            $todaySalesQuery->where('kode_sales', $nik);
            $monthSalesQuery->where('kode_sales', $nik);
        }

        $todaySales = $todaySalesQuery->sum('grand_total');
        $monthSales = $monthSalesQuery->sum('grand_total');

        $orders = $query->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('mobile.history', compact('orders', 'q', 'filter', 'todaySales', 'monthSales'));
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
            'tanggal'           => 'required|date',
            'tanggal_kirim'     => 'nullable|date',
            'kode_pelanggan'    => 'required|string|exists:pelanggan,kode_pelanggan',
            'jenis_transaksi'   => [
                'required',
                'in:Tunai,Kredit',
                function ($attribute, $value, $fail) {
                    if (Auth::user()->jenis_sales == '1' && $value === 'Kredit') {
                        $fail('Salesman dengan tipe ini tidak diizinkan untuk melakukan transaksi Kredit.');
                    }
                }
            ],
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
            $overdueInvoices = $pelanggan->getOverdueInvoices()->pluck('no_faktur')->implode(', ');
            return redirect()->back()->withInput()->with('error', "Transaksi ditolak. Pelanggan {$pelanggan->nama_pelanggan} memiliki faktur overdue: {$overdueInvoices}!");
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

        // Fetch active diskon strata rules for recalculating
        $diskonStrata = DiskonStrata::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('berlaku_dari')->orWhere('berlaku_dari', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', now());
            })
            ->with(['details', 'barangs', 'kategori', 'merk'])
            ->get();

        // Calculate supplier subtotals for supplier-level strata
        $supplierSubtotals = [];
        foreach ($request->items as $row) {
            $barang = Barang::find($row['kode_barang']);
            if ($barang && $barang->kode_supplier) {
                $qty = floatval($row['qty']);
                $harga = floatval($row['harga']);
                $sub = $qty * $harga;
                $supplierSubtotals[$barang->kode_supplier] = ($supplierSubtotals[$barang->kode_supplier] ?? 0) + $sub;
            }
        }

        // Strata matching closure matching JS logic
        $calculateStrata = function($barangCode, $qty, $sub, $barang) use ($diskonStrata, $supplierSubtotals, $request) {
            if (!$barang) return ['d1' => 0, 'd2' => 0];

            $bestRate = 0;
            $bestRule = null;
            $bestDetail = null;

            $checkRule = function($r, $d) use (&$bestRate, &$bestRule, &$bestDetail) {
                $rate = floatval($d->dis1 ?? 0);
                if ($rate >= $bestRate) {
                    $bestRate = $rate;
                    $bestRule = $r;
                    $bestDetail = $d;
                }
            };

            // Priority 1: Per Barang
            foreach ($diskonStrata as $r) {
                if ($r->tipe === 'barang') {
                    if ($r->barangs && $r->barangs->contains('kode_barang', $barangCode)) {
                        foreach ($r->details as $d) {
                            $minQty = floatval($d->min_qty ?? 0);
                            $maxQty = $d->max_qty !== null ? floatval($d->max_qty) : null;
                            if ($qty >= $minQty && ($maxQty === null || $qty <= $maxQty)) {
                                $checkRule($r, $d);
                            }
                        }
                    }
                }
            }

            // Priority 2: Per Beberapa Barang
            if (!$bestRule) {
                foreach ($diskonStrata as $r) {
                    if ($r->tipe === 'beberapa_barang') {
                        if ($r->barangs && $r->barangs->contains('kode_barang', $barangCode)) {
                            foreach ($r->details as $d) {
                                $minQty = floatval($d->min_qty ?? 0);
                                $maxQty = $d->max_qty !== null ? floatval($d->max_qty) : null;
                                if ($qty >= $minQty && ($maxQty === null || $qty <= $maxQty)) {
                                    $checkRule($r, $d);
                                }
                            }
                        }
                    }
                }
            }

            // Priority 3: Per Kategori
            if (!$bestRule && $barang->kategori) {
                foreach ($diskonStrata as $r) {
                    if ($r->tipe === 'kategori') {
                        if ($r->kategori && $r->kategori->nama_kategori === $barang->kategori) {
                            foreach ($r->details as $d) {
                                $minQty = floatval($d->min_qty ?? 0);
                                $maxQty = $d->max_qty !== null ? floatval($d->max_qty) : null;
                                if ($qty >= $minQty && ($maxQty === null || $qty <= $maxQty)) {
                                    $checkRule($r, $d);
                                }
                            }
                        }
                    }
                }
            }

            // Priority 4: Per Merk
            if (!$bestRule && $barang->merk) {
                foreach ($diskonStrata as $r) {
                    if ($r->tipe === 'merk') {
                        if ($r->merk && $r->merk->nama_merk === $barang->merk) {
                            foreach ($r->details as $d) {
                                $minQty = floatval($d->min_qty ?? 0);
                                $maxQty = $d->max_qty !== null ? floatval($d->max_qty) : null;
                                if ($qty >= $minQty && ($maxQty === null || $qty <= $maxQty)) {
                                    $checkRule($r, $d);
                                }
                            }
                        }
                    }
                }
            }

            // Priority 5: Per Supplier
            if (!$bestRule && $barang->kode_supplier) {
                $totalSupplierNominal = $supplierSubtotals[$barang->kode_supplier] ?? 0;
                foreach ($diskonStrata as $r) {
                    if ($r->tipe === 'supplier') {
                        if ($r->kode_supplier === $barang->kode_supplier) {
                            foreach ($r->details as $d) {
                                $minNom = floatval($d->min_nominal ?? 0);
                                $maxNom = $d->max_nominal !== null ? floatval($d->max_nominal) : null;
                                if ($totalSupplierNominal >= $minNom && ($maxNom === null || $totalSupplierNominal <= $maxNom)) {
                                    $checkRule($r, $d);
                                }
                            }
                        }
                    }
                }
            }

            // Convert raw values to percentage
            $d1_pct = 0;
            $d2_pct = 0;

            if ($bestRule && $bestDetail) {
                $rawDis1 = floatval($bestDetail->dis1 ?? 0);
                $rawDis2 = floatval($bestDetail->dis2 ?? 0);

                if ($bestDetail->tipe_nilai === 'persen') {
                    $d1_pct = $rawDis1;
                    $d2_pct = $rawDis2;
                } else {
                    if ($bestRule->tipe === 'supplier') {
                        $totalSupplierNominal = $supplierSubtotals[$barang->kode_supplier] ?? 1;
                        if ($totalSupplierNominal <= 0) $totalSupplierNominal = 1;
                        $d1_pct = ($rawDis1 / $totalSupplierNominal) * 100;
                        $d2_pct = ($rawDis2 / $totalSupplierNominal) * 100;
                    } else {
                        if ($sub > 0) {
                            $d1_pct = ($rawDis1 / $sub) * 100;
                            $d2_pct = ($rawDis2 / $sub) * 100;
                        }
                    }
                }

                // D2 only if Tunai
                if ($request->jenis_transaksi !== 'Tunai') {
                    $d2_pct = 0;
                }
            }

            return [
                'd1' => round($d1_pct, 5),
                'd2' => round($d2_pct, 5)
            ];
        };

        // Calculate Grand Total for validation using recalculated strata discounts
        $tempSubtotalSum = 0;
        $tempTotalDiskon = 0;
        foreach ($request->items as $row) {
            $barang = Barang::find($row['kode_barang']);
            $sub = $row['qty'] * $row['harga'];
            $strata = $calculateStrata($row['kode_barang'], $row['qty'], $sub, $barang);
            
            $d1_pct = $strata['d1'];
            $d2_pct = $strata['d2'];
            $d3_pct = 0; // Salesman cannot input manual D3 discount

            $d1 = $sub * ($d1_pct / 100);
            $d2 = ($sub - $d1) * ($d2_pct / 100);
            $d3 = ($sub - $d1 - $d2) * ($d3_pct / 100);
            $tempSubtotalSum += $sub;
            $tempTotalDiskon += round($d1 + $d2 + $d3, 2);
        }
        $tempGrandTotal = $tempSubtotalSum - $tempTotalDiskon; // Salesman cannot input global discount

        // 2. Verify Credit Limit
        if ($request->jenis_transaksi === 'Kredit') {
            $sisaLimit = $pelanggan->getSisaLimitKredit();
            if ($tempGrandTotal > $sisaLimit) {
                return redirect()->back()->withInput()->with('error', "Limit kredit terlampaui! Sisa limit: Rp " . number_format($sisaLimit, 0, ',', '.'));
            }
        }

        // 3. Process Transaction
        try {
            $savedNoFaktur = DB::transaction(function () use ($request, $tempGrandTotal, $pelanggan, $calculateStrata) {
                // === Generate no_faktur secara atomik (mencegah race condition) ===
                $todayDate = date('my');
                $lastFaktur = Penjualan::where('no_faktur', 'like', '%-PJ-MJ-' . $todayDate)
                    ->lockForUpdate()
                    ->orderBy('no_faktur', 'desc')
                    ->first();
                $nextNum = $lastFaktur ? (intval(substr($lastFaktur->no_faktur, 0, 4)) + 1) : 1;
                $noFaktur = str_pad($nextNum, 4, '0', STR_PAD_LEFT) . '-PJ-MJ-' . $todayDate;

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
                    \App\Models\StokMutasi::log(
                        $row['kode_barang'],
                        $request->tanggal,
                        'Penjualan',
                        $noFaktur,
                        0,
                        $qtySmallest,
                        Auth::id(),
                        'Penjualan via Mobile'
                    );

                    $subtotal    = $row['qty'] * $row['harga'];
                    
                    // Recalculate strata discounts
                    $strata      = $calculateStrata($row['kode_barang'], $row['qty'], $subtotal, $barang);
                    $d1_pct      = $strata['d1'];
                    $d2_pct      = $strata['d2'];
                    $d3_pct      = 0; // Salesman cannot input manual D3 discount

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

                $diskonGlobal = 0; // Salesman cannot input global discount
                $grandTotal   = $subtotalSum - $totalDiskon - $diskonGlobal;

                // Save Penjualan (Order)
                $penjualan = Penjualan::create([
                    'no_faktur'       => $noFaktur,
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

                \App\Models\ActivityLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'action' => 'Tambah Penjualan (Mobile)',
                    'description' => $penjualan->no_faktur . ' (Pelanggan: ' . $penjualan->kode_pelanggan . ')',
                    'ip_address' => $request->ip(),
                    'no_faktur' => $penjualan->no_faktur,
                ]);

                return $penjualan->no_faktur;
            });
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('mobile.kunjungan.index')->with('success', 'Pesanan penjualan ' . $savedNoFaktur . ' berhasil disimpan.');
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
