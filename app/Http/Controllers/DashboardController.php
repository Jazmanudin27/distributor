<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\Setting;
use App\Models\Pembelian;
use App\Models\Barang;
use App\Models\AjuanLimitKredit;
use App\Models\PenjualanCheckin;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        // 1. Stats Cards
        $totalPenjualanHariIni = (float) Penjualan::where('batal', 0)
            ->where('tanggal', $today)
            ->sum('grand_total');

        $totalPenjualanBulanIni = (float) Penjualan::where('batal', 0)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('grand_total');

        $totalPembelianHariIni = Pembelian::where('tanggal', $today)
            ->sum('grand_total');

        // Total Piutang (Outstanding)
        $totalPiutang = Penjualan::where('batal', 0)
            ->selectRaw('SUM(grand_total - (
                COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran WHERE penjualan_pembayaran.no_faktur = penjualan.no_faktur), 0) + 
                COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_transfer WHERE penjualan_pembayaran_transfer.no_faktur = penjualan.no_faktur), 0) + 
                COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_giro WHERE penjualan_pembayaran_giro.no_faktur = penjualan.no_faktur), 0)
            )) as outstanding')
            ->value('outstanding') ?: 0;

        $pendingApprovalKredit = AjuanLimitKredit::where('status', 'pending')->count();

        $lowStockCount = Barang::where('status', 1)
            ->where('stok', '<=', 10)
            ->count();

        // 2. Charts Data
        // Monthly Sales (Last 6 Months)
        $monthlySales = Penjualan::where('batal', 0)
            ->where('tanggal', '>=', Carbon::now()->subMonths(5)->startOfMonth()->toDateString())
            ->select(
                DB::raw("DATE_FORMAT(tanggal, '%M %Y') as month_name"),
                DB::raw("DATE_FORMAT(tanggal, '%Y-%m') as month_key"),
                DB::raw("SUM(grand_total) as total")
            )
            ->groupBy('month_key', 'month_name')
            ->orderBy('month_key', 'asc')
            ->get();

        // Top 5 Sales per Salesman (Current Month)
        $topSalesmen = Penjualan::join('users', 'penjualan.kode_sales', '=', 'users.nik')
            ->where('penjualan.batal', 0)
            ->whereMonth('penjualan.tanggal', Carbon::now()->month)
            ->whereYear('penjualan.tanggal', Carbon::now()->year)
            ->select(
                'users.name',
                DB::raw("SUM(penjualan.grand_total) as total")
            )
            ->groupBy('users.nik', 'users.name')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        // 3. Lists
        // Active Salesmen Checked-in Today
        $activeCheckins = PenjualanCheckin::where('tanggal', $today)
            ->with(['sales', 'pelanggan'])
            ->orderBy('checkin', 'desc')
            ->get();

        // Low Stock Items List
        $lowStockItems = Barang::where('status', 1)
            ->where('stok', '<=', 10)
            ->with('satuans')
            ->orderBy('stok', 'asc')
            ->take(5)
            ->get();

        // Latest Credit Limit Requests
        $latestLimitRequests = AjuanLimitKredit::with(['pelanggan', 'requester'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $targetPenjualan = (float)Setting::getVal('target_penjualan_bulan_ini', 5000000000);
        $progressPenjualan = $targetPenjualan > 0 ? ($totalPenjualanBulanIni / $targetPenjualan) * 100 : 0;
        $lockAdmin = Setting::getVal('lock_penjualan_admin', '0') === '1';
        $lockSales = Setting::getVal('lock_penjualan_sales', '0') === '1';

        return view('welcome', compact(
            'totalPenjualanHariIni',
            'totalPenjualanBulanIni',
            'totalPembelianHariIni',
            'totalPiutang',
            'pendingApprovalKredit',
            'lowStockCount',
            'monthlySales',
            'topSalesmen',
            'activeCheckins',
            'lowStockItems',
            'latestLimitRequests',
            'targetPenjualan',
            'progressPenjualan',
            'lockAdmin',
            'lockSales'
        ));
    }

    public function setTarget(Request $request)
    {
        $request->validate([
            'target_penjualan' => 'required|numeric|min:0',
        ]);

        Setting::setVal('target_penjualan_bulan_ini', $request->target_penjualan);

        return redirect()->back()->with('success', 'Target penjualan bulan ini berhasil diperbarui.');
    }

    public function setLockPenjualan(Request $request)
    {
        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('Owner')) {
            abort(403, 'Unauthorized action.');
        }

        Setting::setVal('lock_penjualan_admin', $request->has('lock_penjualan_admin') ? '1' : '0');
        Setting::setVal('lock_penjualan_sales', $request->has('lock_penjualan_sales') ? '1' : '0');

        return redirect()->back()->with('success', 'Pengaturan kunci input penjualan berhasil diperbarui.');
    }
}
