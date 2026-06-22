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

        return view('mobile.owner.laba_rugi', compact(
            'tanggal_mulai', 'tanggal_akhir',
            'salesGross', 'salesReturn', 'salesNet',
            'hppGross', 'hppReturn', 'hppNet',
            'profit', 'marginPercent'
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
}
