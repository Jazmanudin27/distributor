<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\PenjualanCheckin;
use App\Models\Pelanggan;
use App\Models\AjuanLimitKredit;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MobileDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $nik = $user->nik;
        $role = strtolower($user->role ?? '');
        $isSpv = ($role === 'spv sales');

        // Start and end of the current month
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        $today = Carbon::now()->toDateString();

        if ($isSpv) {
            // Achieved sales of all sales this month
            $achievedSales = Penjualan::where('batal', 0)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->sum('grand_total');

            // Today's sales of all sales
            $todaySales = Penjualan::where('batal', 0)
                ->whereDate('tanggal', $today)
                ->sum('grand_total');

            // Today's visits count of all sales
            $todayVisitsCount = PenjualanCheckin::whereDate('checkin', $today)
                ->count();

            // Recent orders of all sales
            $recentOrders = Penjualan::with(['pelanggan.wilayah', 'pelanggan.subWilayah', 'sales', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        } else {
            // 1. Achieved sales this month
            $achievedSales = Penjualan::where('kode_sales', $nik)
                ->where('batal', 0)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->sum('grand_total');

            // 2. Today's sales
            $todaySales = Penjualan::where('kode_sales', $nik)
                ->where('batal', 0)
                ->whereDate('tanggal', $today)
                ->sum('grand_total');

            // 3. Today's visits count
            $todayVisitsCount = PenjualanCheckin::where('kode_sales', $nik)
                ->whereDate('checkin', $today)
                ->count();

            // 5. Recent orders
            $recentOrders = Penjualan::with(['pelanggan.wilayah', 'pelanggan.subWilayah', 'sales', 'user'])
                ->where('kode_sales', $nik)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        // 2. Target sales this month (Disabled)
        $targetAmount = 0;

        // 4. Total registered customers
        $totalCustomers = Pelanggan::where('status', '1')->count();

        // 6. Active check-in
        $activeCheckin = PenjualanCheckin::with('pelanggan')
            ->where('kode_sales', $nik)
            ->whereNull('checkout')
            ->first();

        // 7. Pending Customer Approvals (for SPV Sales)
        $pendingCustomersCount = 0;
        $pendingLimitCount = 0;
        $pendingPembelianCount = 0;
        if ($isSpv) {
            $pendingCustomersCount = Pelanggan::where(function($q) {
                $q->whereNull('approve')->orWhere('approve', 0);
            })->count();
            $pendingLimitCount = AjuanLimitKredit::where('status', 'pending')->count();
            $pendingPembelianCount = Pembelian::whereNull('tanggal_approve')->count();
        }

        // Target progress percentage
        $progressPercentage = 0;

        return view('mobile.dashboard', compact(
            'achievedSales',
            'todaySales',
            'targetAmount',
            'todayVisitsCount',
            'totalCustomers',
            'recentOrders',
            'progressPercentage',
            'activeCheckin',
            'pendingCustomersCount',
            'pendingLimitCount',
            'pendingPembelianCount'
        ));
    }

    public function profile()
    {
        $user = Auth::user();
        $role = strtolower($user->role ?? '');
        $isSales = in_array($role, ['sales', 'spv sales']);
        $nik = $user->nik;

        // Start and end of the current month
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        // Target sales this month (Disabled)
        $targetAmount = 0;

        // Achieved sales this month
        $achievedSales = 0;
        $totalOrdersCount = 0;
        $totalVisitsCount = 0;

        if ($isSales && $nik) {
            $achievedSales = Penjualan::where('kode_sales', $nik)
                ->where('batal', 0)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->sum('grand_total');

            $totalOrdersCount = Penjualan::where('kode_sales', $nik)->where('batal', 0)->count();
            $totalVisitsCount = PenjualanCheckin::where('kode_sales', $nik)->count();
        }

        // Target progress percentage
        $progressPercentage = 0;

        return view('mobile.profile', compact('user', 'achievedSales', 'targetAmount', 'progressPercentage', 'totalOrdersCount', 'totalVisitsCount'));
    }

    /**
     * Laporan Pencapaian Sales Mobile (untuk SPV Sales)
     */
    public function salesAchievement(Request $request)
    {
        $role = strtolower(Auth::user()->role ?? '');
        if ($role !== 'spv sales') {
            abort(403, 'Akses khusus SPV Sales.');
        }

        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));

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

        return view('mobile.spv.sales_achievement', compact('achievements', 'tanggal_mulai', 'tanggal_akhir'));
    }

    /**
     * Laporan Kunjungan Sales Mobile (untuk SPV Sales)
     */
    public function salesVisits(Request $request)
    {
        $role = strtolower(Auth::user()->role ?? '');
        if ($role !== 'spv sales') {
            abort(403, 'Akses khusus SPV Sales.');
        }

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

        return view('mobile.spv.sales_visits', compact('visits', 'salesmen', 'tanggal_mulai', 'tanggal_akhir', 'selected_sales'));
    }

    /**
     * Halaman List Pending Approval Pembelian untuk SPV Sales
     */
    public function pendingPembelianListSpv()
    {
        $role = strtolower(Auth::user()->role ?? '');
        if ($role !== 'spv sales') {
            abort(403, 'Akses khusus SPV Sales.');
        }

        $pendingPembelians = Pembelian::with(['supplier', 'details.barang'])
            ->whereNull('tanggal_approve')
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('mobile.spv.pembelian_pending', compact('pendingPembelians'));
    }

    /**
     * Approve Pembelian via Mobile (SPV Sales)
     */
    public function approvePembelianSpv(Request $request, $no_faktur)
    {
        $role = strtolower(Auth::user()->role ?? '');
        if ($role !== 'spv sales') {
            abort(403, 'Akses khusus SPV Sales.');
        }

        $pembelian = Pembelian::findOrFail($no_faktur);

        if ($pembelian->tanggal_approve) {
            return redirect()->route('mobile.spv.pembelian.pending')
                ->with('error', 'Transaksi pembelian ini sudah disetujui sebelumnya.');
        }

        $pembelian->update([
            'tanggal_approve' => now(),
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'Approve Pembelian Mobile',
            'description' => $pembelian->no_faktur . ' disetujui oleh SPV.',
            'ip_address' => $request->ip(),
            'no_faktur' => $pembelian->no_faktur,
        ]);

        return redirect()->route('mobile.spv.pembelian.pending')
            ->with('success', "Pembelian '" . $pembelian->no_faktur . "' berhasil disetujui.");
    }
}
