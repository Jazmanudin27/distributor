<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\PenjualanCheckin;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MobileDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $nik = $user->nik;

        // Start and end of the current month
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        $today = Carbon::now()->toDateString();

        // 1. Achieved sales this month
        $achievedSales = Penjualan::where('kode_sales', $nik)
            ->where('batal', 0)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('grand_total');

        // 2. Target sales this month (Disabled)
        $targetAmount = 0;

        // 3. Today's visits count
        $todayVisitsCount = PenjualanCheckin::where('kode_sales', $nik)
            ->whereDate('checkin', $today)
            ->count();

        // 4. Total registered customers
        $totalCustomers = Pelanggan::where('status', '1')->count();

        // 5. Recent orders
        $recentOrders = Penjualan::with(['pelanggan.wilayah', 'pelanggan.subWilayah', 'sales', 'user'])
            ->where('kode_sales', $nik)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 6. Active check-in
        $activeCheckin = PenjualanCheckin::with('pelanggan')
            ->where('kode_sales', $nik)
            ->whereNull('checkout')
            ->first();

        // 7. Pending Customer Approvals (for SPV Sales)
        $pendingCustomersCount = 0;
        if (strtolower($user->role) === 'spv sales') {
            $pendingCustomersCount = Pelanggan::where(function($q) {
                $q->whereNull('approve')->orWhere('approve', 0);
            })->count();
        }

        // Target progress percentage
        $progressPercentage = 0;

        return view('mobile.dashboard', compact(
            'achievedSales',
            'targetAmount',
            'todayVisitsCount',
            'totalCustomers',
            'recentOrders',
            'progressPercentage',
            'activeCheckin',
            'pendingCustomersCount'
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
}
