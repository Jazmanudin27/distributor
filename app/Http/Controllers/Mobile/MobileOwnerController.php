<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\Pembelian;
use App\Models\AjuanLimitKredit;
use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MobileOwnerController extends Controller
{
    /**
     * Dashboard Utama Owner
     */
    public function index()
    {
        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        // 1. Penjualan
        $salesToday = (float) Penjualan::where('batal', 0)->whereDate('tanggal', $today)->sum('grand_total');
        $salesMonth = (float) Penjualan::where('batal', 0)->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->sum('grand_total');

        // 2. Pembelian
        $purchaseToday = (float) Pembelian::whereDate('tanggal', $today)->sum('grand_total');
        $purchaseMonth = (float) Pembelian::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->sum('grand_total');

        // 3. Setoran / Pembayaran Masuk (Cash + Transfer + Giro)
        // Today
        $payCashToday = (float) DB::table('penjualan_pembayaran')->whereDate('tanggal', $today)->sum('jumlah');
        $payTransferToday = (float) DB::table('penjualan_pembayaran_transfer')->whereDate('tanggal', $today)->sum('jumlah');
        $payGiroToday = (float) DB::table('penjualan_pembayaran_giro')->whereDate('tanggal', $today)->sum('jumlah');
        $paymentsToday = $payCashToday + $payTransferToday + $payGiroToday;

        // Month
        $payCashMonth = (float) DB::table('penjualan_pembayaran')->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->sum('jumlah');
        $payTransferMonth = (float) DB::table('penjualan_pembayaran_transfer')->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->sum('jumlah');
        $payGiroMonth = (float) DB::table('penjualan_pembayaran_giro')->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->sum('jumlah');
        $paymentsMonth = $payCashMonth + $payTransferMonth + $payGiroMonth;

        // 4. Laba Kotor Bulan Ini
        $salesReturnMonth = (float) DB::table('retur_penjualan')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('total');
        $salesNetMonth = $salesMonth - $salesReturnMonth;

        $hppGrossMonth = (float) DB::table('penjualan_detail')
            ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
            ->where('penjualan.batal', 0)
            ->whereBetween('penjualan.tanggal', [$startOfMonth, $endOfMonth])
            ->select(DB::raw('SUM(penjualan_detail.qty * penjualan_detail.harga_pokok) as total_hpp'))
            ->first()->total_hpp ?? 0;

        $hppReturnMonth = (float) DB::table('retur_penjualan_detail')
            ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
            ->join('barang_satuan', 'retur_penjualan_detail.id_satuan', '=', 'barang_satuan.id')
            ->whereBetween('retur_penjualan.tanggal', [$startOfMonth, $endOfMonth])
            ->select(DB::raw('SUM(retur_penjualan_detail.qty * barang_satuan.harga_pokok) as total_hpp'))
            ->first()->total_hpp ?? 0;

        if ($hppReturnMonth == 0 && $salesReturnMonth > 0 && $salesMonth > 0) {
            $hppReturnMonth = $salesReturnMonth * ($hppGrossMonth / $salesMonth);
        }
        $hppNetMonth = $hppGrossMonth - $hppReturnMonth;
        $profitMonth = $salesNetMonth - $hppNetMonth;

        // 5. Counts
        $lowStockCount = Barang::where('status', 1)->whereColumn('stok', '<=', 'stok_min')->count();
        $pendingApprovalsCount = AjuanLimitKredit::where('status', 'pending')->count();
        $pendingPelangganCount = Pelanggan::where(function($q) {
            $q->whereNull('approve')->orWhere('approve', 0);
        })->count();

        return view('mobile.owner.dashboard', compact(
            'salesToday', 'salesMonth', 'purchaseToday', 'purchaseMonth', 
            'paymentsToday', 'paymentsMonth', 'profitMonth', 
            'lowStockCount', 'pendingApprovalsCount', 'pendingPelangganCount'
        ));
    }

    /**
     * Halaman Stok Menipis
     */
    public function lowStock()
    {
        $lowStockItems = Barang::with('satuans')
            ->where('status', 1)
            ->whereColumn('stok', '<=', 'stok_min')
            ->orderBy('stok', 'asc')
            ->get();

        return view('mobile.owner.low_stock', compact('lowStockItems'));
    }

    /**
     * Halaman Antrean Persetujuan Limit
     */
    public function pendingApproval()
    {
        $ajuans = AjuanLimitKredit::with(['pelanggan', 'requester'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('mobile.owner.pending_approval', compact('ajuans'));
    }

    /**
     * Approve Limit Kredit via Mobile
     */
    public function approveLimit(Request $request, $id)
    {
        $ajuan = AjuanLimitKredit::findOrFail($id);

        if (!$ajuan->isPending()) {
            return back()->with('error', 'Ajuan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($ajuan, $request) {
            $ajuan->pelanggan->update([
                'limit_pelanggan' => $ajuan->limit_baru,
            ]);

            $ajuan->update([
                'status'       => 'approved',
                'catatan_admin' => $request->catatan_admin,
                'approved_by'  => Auth::id(),
                'approved_at'  => now(),
            ]);
        });

        return redirect()->route('mobile.owner.pending-approval')
            ->with('success', 'Ajuan limit kredit pelanggan berhasil disetujui.');
    }

    /**
     * Reject Limit Kredit via Mobile
     */
    public function rejectLimit(Request $request, $id)
    {
        $request->validate([
            'catatan_admin' => 'required|string|max:500',
        ], [
            'catatan_admin.required' => 'Catatan penolakan wajib diisi.',
        ]);

        $ajuan = AjuanLimitKredit::findOrFail($id);

        if (!$ajuan->isPending()) {
            return back()->with('error', 'Ajuan ini sudah diproses sebelumnya.');
        }

        $ajuan->update([
            'status'        => 'rejected',
            'catatan_admin' => $request->catatan_admin,
            'approved_by'   => Auth::id(),
            'approved_at'   => now(),
        ]);

        return redirect()->route('mobile.owner.pending-approval')
            ->with('success', 'Ajuan limit kredit pelanggan telah ditolak.');
    }

    /**
     * Halaman Laba Rugi Mobile
     */
    public function labaRugi(Request $request)
    {
        // Explicitly block sales and spv sales roles from accessing laba rugi financial metrics
        $role = strtolower(Auth::user()->role ?? '');
        if (in_array($role, ['sales', 'spv sales'])) {
            abort(403, 'Akses ditolak. Anda tidak diizinkan melihat Laporan Laba Rugi.');
        }

        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));

        // 1. Penjualan Kotor
        $salesGross = (float) Penjualan::where('batal', 0)
            ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
            ->sum('grand_total');

        // 2. Retur Penjualan
        $salesReturn = (float) DB::table('retur_penjualan')
            ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
            ->sum('total');

        // 3. Penjualan Bersih
        $salesNet = $salesGross - $salesReturn;

        // 4. HPP Penjualan (qty * harga_pokok)
        $hppGross = (float) DB::table('penjualan_detail')
            ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
            ->where('penjualan.batal', 0)
            ->whereBetween('penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
            ->select(DB::raw('SUM(penjualan_detail.qty * penjualan_detail.harga_pokok) as total_hpp'))
            ->first()->total_hpp ?? 0;

        // 5. HPP Retur Penjualan (qty * barang_satuan.harga_pokok)
        $hppReturn = (float) DB::table('retur_penjualan_detail')
            ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
            ->join('barang_satuan', 'retur_penjualan_detail.id_satuan', '=', 'barang_satuan.id')
            ->whereBetween('retur_penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
            ->select(DB::raw('SUM(retur_penjualan_detail.qty * barang_satuan.harga_pokok) as total_hpp'))
            ->first()->total_hpp ?? 0;

        if ($hppReturn == 0 && $salesReturn > 0 && $salesGross > 0) {
            $hppReturn = $salesReturn * ($hppGross / $salesGross);
        }

        // 6. HPP Bersih
        $hppNet = $hppGross - $hppReturn;

        // 7. Laba Kotor
        $profit = $salesNet - $hppNet;

        // 8. Margin Laba
        $marginPercent = $salesNet > 0 ? ($profit / $salesNet) * 100 : 0;

        // Breakdown Per Tanggal & Per Sales
        $dailyBreakdown = [];
        $salesBreakdown = [];

        $penjualanList = DB::table('penjualan')
            ->where('batal', 0)
            ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
            ->select('no_faktur', 'tanggal', 'kode_sales', 'grand_total')
            ->get();

        $penjualanHppList = DB::table('penjualan_detail')
            ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
            ->where('penjualan.batal', 0)
            ->whereBetween('penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
            ->select('penjualan.no_faktur', DB::raw('SUM(penjualan_detail.qty * penjualan_detail.harga_pokok) as total_hpp'))
            ->groupBy('penjualan.no_faktur')
            ->get()->keyBy('no_faktur');

        $returList = DB::table('retur_penjualan')
            ->leftJoin('penjualan', 'retur_penjualan.no_faktur', '=', 'penjualan.no_faktur')
            ->whereBetween('retur_penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
            ->select('retur_penjualan.no_retur', 'retur_penjualan.tanggal', 'retur_penjualan.total as grand_total', 'penjualan.kode_sales')
            ->get();

        $returHppList = DB::table('retur_penjualan_detail')
            ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
            ->join('barang_satuan', 'retur_penjualan_detail.id_satuan', '=', 'barang_satuan.id')
            ->whereBetween('retur_penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
            ->select('retur_penjualan.no_retur', DB::raw('SUM(retur_penjualan_detail.qty * barang_satuan.harga_pokok) as total_hpp'))
            ->groupBy('retur_penjualan.no_retur')
            ->get()->keyBy('no_retur');

        $users = \App\Models\User::pluck('name', 'nik');

        foreach ($penjualanList as $p) {
            $date = $p->tanggal;
            $salesNik = $p->kode_sales ?: 'UMUM';
            $salesName = $users[$salesNik] ?? ($salesNik == 'UMUM' ? 'Tanpa Sales' : $salesNik);
            $hpp = $penjualanHppList[$p->no_faktur]->total_hpp ?? 0;
            
            if (!isset($dailyBreakdown[$date])) $dailyBreakdown[$date] = ['salesGross' => 0, 'salesReturn' => 0, 'hppGross' => 0, 'hppReturn' => 0];
            $dailyBreakdown[$date]['salesGross'] += $p->grand_total;
            $dailyBreakdown[$date]['hppGross'] += $hpp;

            if (!isset($salesBreakdown[$salesName])) $salesBreakdown[$salesName] = ['salesGross' => 0, 'salesReturn' => 0, 'hppGross' => 0, 'hppReturn' => 0];
            $salesBreakdown[$salesName]['salesGross'] += $p->grand_total;
            $salesBreakdown[$salesName]['hppGross'] += $hpp;
        }

        foreach ($returList as $r) {
            $date = $r->tanggal;
            $salesNik = $r->kode_sales ?: 'UMUM';
            $salesName = $users[$salesNik] ?? ($salesNik == 'UMUM' ? 'Tanpa Sales' : $salesNik);
            $hpp = $returHppList[$r->no_retur]->total_hpp ?? 0;
            
            if ($hpp == 0 && $r->grand_total > 0 && $salesGross > 0) {
                 $hpp = $r->grand_total * ($hppGross / $salesGross);
            }

            if (!isset($dailyBreakdown[$date])) $dailyBreakdown[$date] = ['salesGross' => 0, 'salesReturn' => 0, 'hppGross' => 0, 'hppReturn' => 0];
            $dailyBreakdown[$date]['salesReturn'] += $r->grand_total;
            $dailyBreakdown[$date]['hppReturn'] += $hpp;

            if (!isset($salesBreakdown[$salesName])) $salesBreakdown[$salesName] = ['salesGross' => 0, 'salesReturn' => 0, 'hppGross' => 0, 'hppReturn' => 0];
            $salesBreakdown[$salesName]['salesReturn'] += $r->grand_total;
            $salesBreakdown[$salesName]['hppReturn'] += $hpp;
        }

        $processBreakdown = function(&$array) {
            foreach ($array as $k => $v) {
                $netSales = $v['salesGross'] - $v['salesReturn'];
                $netHpp = $v['hppGross'] - $v['hppReturn'];
                $prof = $netSales - $netHpp;
                $array[$k]['netSales'] = $netSales;
                $array[$k]['netHpp'] = $netHpp;
                $array[$k]['profit'] = $prof;
                $array[$k]['margin'] = $netSales > 0 ? ($prof / $netSales) * 100 : 0;
            }
        };

        $processBreakdown($dailyBreakdown);
        $processBreakdown($salesBreakdown);

        krsort($dailyBreakdown);
        uasort($salesBreakdown, function($a, $b) { return $b['profit'] <=> $a['profit']; });

        // Supplier-wise query logic
        $supplierNames = \App\Models\Supplier::pluck('nama_supplier', 'kode_supplier')->toArray();
        
        $itemSalesQuery = DB::table('penjualan_detail')
            ->join('penjualan', 'penjualan_detail.no_faktur', '=', 'penjualan.no_faktur')
            ->join('barang', 'penjualan_detail.kode_barang', '=', 'barang.kode_barang')
            ->leftJoin('barang_satuan', 'penjualan_detail.satuan_id', '=', 'barang_satuan.id')
            ->where('penjualan.batal', 0)
            ->whereBetween('penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
            ->select(
                'barang.kode_supplier',
                'barang.kode_barang',
                'barang.nama_barang',
                'barang_satuan.satuan',
                DB::raw('SUM(penjualan_detail.qty) as total_qty'),
                DB::raw('SUM(penjualan_detail.total) as total_sales'),
                DB::raw('SUM(penjualan_detail.qty * penjualan_detail.harga_pokok) as total_hpp')
            )
            ->groupBy('barang.kode_supplier', 'barang.kode_barang', 'barang.nama_barang', 'barang_satuan.satuan')
            ->get();
        
        $itemReturnsQuery = DB::table('retur_penjualan_detail')
            ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
            ->join('barang', 'retur_penjualan_detail.kode_barang', '=', 'barang.kode_barang')
            ->leftJoin('barang_satuan', 'retur_penjualan_detail.id_satuan', '=', 'barang_satuan.id')
            ->whereBetween('retur_penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
            ->select(
                'barang.kode_supplier',
                'barang.kode_barang',
                DB::raw('SUM(retur_penjualan_detail.qty) as total_qty'),
                DB::raw('SUM(retur_penjualan_detail.subtotal_retur - COALESCE(retur_penjualan_detail.total_diskon_rupiah, 0)) as total_return'),
                DB::raw('SUM(retur_penjualan_detail.qty * barang_satuan.harga_pokok) as total_hpp_return')
            )
            ->groupBy('barang.kode_supplier', 'barang.kode_barang')
            ->get()
            ->keyBy(function($item) {
                return $item->kode_supplier . '_' . $item->kode_barang;
            });

        $returnsBySupplier = DB::table('retur_penjualan_detail')
            ->join('retur_penjualan', 'retur_penjualan_detail.no_retur', '=', 'retur_penjualan.no_retur')
            ->join('barang', 'retur_penjualan_detail.kode_barang', '=', 'barang.kode_barang')
            ->leftJoin('barang_satuan', 'retur_penjualan_detail.id_satuan', '=', 'barang_satuan.id')
            ->whereBetween('retur_penjualan.tanggal', [$tanggal_mulai, $tanggal_akhir])
            ->select(
                'barang.kode_supplier',
                DB::raw('SUM(retur_penjualan_detail.subtotal_retur - COALESCE(retur_penjualan_detail.total_diskon_rupiah, 0)) as total_return'),
                DB::raw('SUM(retur_penjualan_detail.qty * barang_satuan.harga_pokok) as total_hpp_return')
            )
            ->groupBy('barang.kode_supplier')
            ->get()
            ->keyBy('kode_supplier');

        $supplierBreakdown = [];

        foreach ($itemSalesQuery as $sale) {
            $supCode = $sale->kode_supplier ?: 'TANPA_SUPPLIER';
            $supName = $supplierNames[$sale->kode_supplier] ?? ($sale->kode_supplier ? $sale->kode_supplier : 'Tanpa Supplier');
            
            if (!isset($supplierBreakdown[$supCode])) {
                $supplierBreakdown[$supCode] = [
                    'name' => $supName,
                    'salesGross' => 0,
                    'salesReturn' => 0,
                    'hppGross' => 0,
                    'hppReturn' => 0,
                    'items' => []
                ];
            }
            
            $supplierBreakdown[$supCode]['salesGross'] += $sale->total_sales;
            $supplierBreakdown[$supCode]['hppGross'] += $sale->total_hpp;
            
            $retKey = $sale->kode_supplier . '_' . $sale->kode_barang;
            $retQty = 0;
            $retVal = 0;
            $retHpp = 0;
            if (isset($itemReturnsQuery[$retKey])) {
                $retItem = $itemReturnsQuery[$retKey];
                $retQty = $retItem->total_qty;
                $retVal = $retItem->total_return;
                $retHpp = $retItem->total_hpp_return;
            }
            
            $netSales = $sale->total_sales - $retVal;
            $netHpp = $sale->total_hpp - $retHpp;
            $profit = $netSales - $netHpp;
            $margin = $netSales > 0 ? ($profit / $netSales) * 100 : 0;
            
            $supplierBreakdown[$supCode]['items'][] = [
                'kode_barang' => $sale->kode_barang,
                'nama_barang' => $sale->nama_barang,
                'satuan' => $sale->satuan ?: 'PCS',
                'qty_sales' => $sale->total_qty,
                'qty_return' => $retQty,
                'netSales' => $netSales,
                'netHpp' => $netHpp,
                'profit' => $profit,
                'margin' => $margin
            ];
        }

        foreach ($returnsBySupplier as $code => $ret) {
            $supCode = $code ?: 'TANPA_SUPPLIER';
            $supName = $supplierNames[$code] ?? ($code ? $code : 'Tanpa Supplier');
            
            if (!isset($supplierBreakdown[$supCode])) {
                $supplierBreakdown[$supCode] = [
                    'name' => $supName,
                    'salesGross' => 0,
                    'salesReturn' => 0,
                    'hppGross' => 0,
                    'hppReturn' => 0,
                    'items' => []
                ];
            }
            
            $supplierBreakdown[$supCode]['salesReturn'] += $ret->total_return;
            $supplierBreakdown[$supCode]['hppReturn'] += $ret->total_hpp_return;
        }

        foreach ($supplierBreakdown as $code => &$data) {
            $netSales = $data['salesGross'] - $data['salesReturn'];
            $netHpp = $data['hppGross'] - $data['hppReturn'];
            $profit = $netSales - $netHpp;
            $margin = $netSales > 0 ? ($profit / $netSales) * 100 : 0;
            
            $data['netSales'] = $netSales;
            $data['netHpp'] = $netHpp;
            $data['profit'] = $profit;
            $data['margin'] = $margin;
            
            usort($data['items'], function($a, $b) {
                return $b['netSales'] <=> $a['netSales'];
            });
        }
        unset($data);

        uasort($supplierBreakdown, function($a, $b) {
            return $b['profit'] <=> $a['profit'];
        });

        return view('mobile.owner.laba_rugi', compact(
            'tanggal_mulai', 'tanggal_akhir',
            'salesGross', 'salesReturn', 'salesNet',
            'hppGross', 'hppReturn', 'hppNet',
            'profit', 'marginPercent',
            'dailyBreakdown', 'salesBreakdown', 'supplierBreakdown'
        ));
    }

    /**
     * Laporan Pencapaian Sales Mobile
     */
    public function salesAchievement(Request $request)
    {
        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));

        \Illuminate\Support\Facades\Log::info('salesAchievement request parameters', [
            'url' => $request->fullUrl(),
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_akhir' => $tanggal_akhir,
        ]);

        \Illuminate\Support\Facades\DB::enableQueryLog();

        // Query sales users
        $salesList = \App\Models\User::whereIn('role', ['sales', 'spv sales'])
            ->where('status', '1')
            ->get();

        $achievements = [];
        foreach ($salesList as $sales) {
            $totalSales = (float) Penjualan::where('kode_sales', $sales->nik)
                ->where('batal', 0)
                ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                ->sum('grand_total');

            $invoiceCount = Penjualan::where('kode_sales', $sales->nik)
                ->where('batal', 0)
                ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                ->count();

            $visitCount = \App\Models\PenjualanCheckin::where('kode_sales', $sales->nik)
                ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir])
                ->count();

            $achievements[] = [
                'name' => $sales->name,
                'nik' => $sales->nik,
                'total_sales' => $totalSales,
                'invoice_count' => $invoiceCount,
                'visit_count' => $visitCount,
            ];
        }

        // Sort by total_sales descending
        usort($achievements, function ($a, $b) {
            return $b['total_sales'] <=> $a['total_sales'];
        });

        \Illuminate\Support\Facades\Log::info('salesAchievement query log', [
            'queries' => \Illuminate\Support\Facades\DB::getQueryLog(),
            'achievements' => $achievements,
        ]);

        return view('mobile.owner.sales_achievement', compact('achievements', 'tanggal_mulai', 'tanggal_akhir'));
    }

    /**
     * Laporan Kunjungan Sales Mobile
     */
    public function salesVisits(Request $request)
    {
        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-d'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $selected_sales = $request->input('kode_sales', '');

        $query = \App\Models\PenjualanCheckin::with(['sales', 'pelanggan.wilayah'])
            ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);

        if ($selected_sales !== '') {
            $query->where('kode_sales', $selected_sales);
        }

        $visits = $query->orderBy('checkin', 'desc')->paginate(20)->appends($request->query());

        $salesmen = \App\Models\User::whereIn('role', ['sales', 'spv sales'])
            ->where('status', '1')
            ->orderBy('name')
            ->get();

        return view('mobile.owner.sales_visits', compact('visits', 'salesmen', 'tanggal_mulai', 'tanggal_akhir', 'selected_sales'));
    }

    /**
     * Halaman Antrean Persetujuan Pelanggan Baru
     */
    public function pendingPelanggan()
    {
        $pendingCustomers = Pelanggan::with(['wilayah', 'subWilayah'])
            ->where(function($q) {
                $q->whereNull('approve')->orWhere('approve', 0);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mobile.owner.pending_pelanggan', compact('pendingCustomers'));
    }

    /**
     * Setujui Pendaftaran Pelanggan Baru
     */
    public function approvePelanggan($kode_pelanggan)
    {
        $pelanggan = Pelanggan::findOrFail($kode_pelanggan);
        $pelanggan->update([
            'approve' => 1,
            'status' => 1,
        ]);

        return redirect()->route('mobile.owner.pending-pelanggan')
            ->with('success', "Pelanggan '{$pelanggan->nama_pelanggan}' berhasil disetujui!");
    }

    /**
     * Tolak Pendaftaran Pelanggan Baru
     */
    public function rejectPelanggan($kode_pelanggan)
    {
        $pelanggan = Pelanggan::findOrFail($kode_pelanggan);
        $pelanggan->update([
            'approve' => 2,
        ]);

        return redirect()->route('mobile.owner.pending-pelanggan')
            ->with('warning', "Pendaftaran pelanggan '{$pelanggan->nama_pelanggan}' ditolak.");
    }

    /**
     * Laporan Orderan / Penjualan Mobile untuk Owner
     */
    public function orders(Request $request)
    {
        $q = $request->input('q');
        $selected_sales = $request->input('kode_sales', '');
        $selected_pelanggan = $request->input('kode_pelanggan', '');
        $filter = $request->input('filter', 'all');

        $query = Penjualan::with(['pelanggan.wilayah', 'sales', 'details.barang', 'details.barangSatuan', 'pembayarans']);

        if ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('no_faktur', 'like', "%{$q}%")
                    ->orWhereHas('pelanggan', function($custQuery) use ($q) {
                        $custQuery->where('nama_pelanggan', 'like', "%{$q}%")
                                  ->orWhere('kode_pelanggan', 'like', "%{$q}%");
                    });
            });
        }

        if ($selected_sales !== '') {
            $query->where('kode_sales', $selected_sales);
        }

        if ($selected_pelanggan !== '') {
            $query->where('kode_pelanggan', $selected_pelanggan);
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

        $orders = $query->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Calculate summary for context
        $todaySalesQuery = Penjualan::where('batal', 0)->whereDate('tanggal', $todayStr);
        if ($selected_sales !== '') $todaySalesQuery->where('kode_sales', $selected_sales);
        if ($selected_pelanggan !== '') $todaySalesQuery->where('kode_pelanggan', $selected_pelanggan);
        $todaySales = $todaySalesQuery->sum('grand_total');

        $monthSalesQuery = Penjualan::where('batal', 0)->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);
        if ($selected_sales !== '') $monthSalesQuery->where('kode_sales', $selected_sales);
        if ($selected_pelanggan !== '') $monthSalesQuery->where('kode_pelanggan', $selected_pelanggan);
        $monthSales = $monthSalesQuery->sum('grand_total');

        // Fetch lists for filter dropdowns
        $salesmen = \App\Models\User::whereIn('role', ['sales', 'spv sales'])
            ->where('status', '1')
            ->orderBy('name')
            ->get();

        $pelanggans = Pelanggan::orderBy('nama_pelanggan')->get();

        return view('mobile.owner.orders', compact('orders', 'salesmen', 'pelanggans', 'q', 'selected_sales', 'selected_pelanggan', 'filter', 'todaySales', 'monthSales'));
    }
}
