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
        $totalPenjualanHariIni = (float) DB::table('penjualan_detail as d')
            ->join('penjualan as p', 'p.no_faktur', '=', 'd.no_faktur')
            ->where('p.batal', 0)
            ->where('p.tanggal', $today)
            ->where('d.is_promo', 0)
            ->sum(DB::raw('(d.qty * d.harga) - d.total_diskon'));

        $totalPenjualanBulanIni = (float) DB::table('penjualan_detail as d')
            ->join('penjualan as p', 'p.no_faktur', '=', 'd.no_faktur')
            ->where('p.batal', 0)
            ->whereBetween('p.tanggal', [$startOfMonth, $endOfMonth])
            ->where('d.is_promo', 0)
            ->sum(DB::raw('(d.qty * d.harga) - d.total_diskon'));

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
        $monthlySales = DB::table('penjualan_detail as d')
            ->join('penjualan as p', 'p.no_faktur', '=', 'd.no_faktur')
            ->where('p.batal', 0)
            ->where('d.is_promo', 0)
            ->where('p.tanggal', '>=', Carbon::now()->subMonths(5)->startOfMonth()->toDateString())
            ->select(
                DB::raw("DATE_FORMAT(p.tanggal, '%M %Y') as month_name"),
                DB::raw("DATE_FORMAT(p.tanggal, '%Y-%m') as month_key"),
                DB::raw("SUM((d.qty * d.harga) - d.total_diskon) as total")
            )
            ->groupBy('month_key', 'month_name')
            ->orderBy('month_key', 'asc')
            ->get();

        // Top 5 Sales per Salesman (Current Month)
        $topSalesmen = DB::table('penjualan_detail as d')
            ->join('penjualan as p', 'p.no_faktur', '=', 'd.no_faktur')
            ->join('users', 'p.kode_sales', '=', 'users.nik')
            ->where('p.batal', 0)
            ->where('d.is_promo', 0)
            ->whereMonth('p.tanggal', Carbon::now()->month)
            ->whereYear('p.tanggal', Carbon::now()->year)
            ->select(
                'users.name',
                DB::raw("SUM((d.qty * d.harga) - d.total_diskon) as total")
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
            'progressPenjualan'
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
}
