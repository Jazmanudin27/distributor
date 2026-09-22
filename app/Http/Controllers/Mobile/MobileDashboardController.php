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
    public function index(Request $request)
    {
        $user = Auth::user();
        $nik = $user->nik;
        $role = strtolower($user->role ?? '');
        $isSpv = in_array($role, ['spv sales', 'spv sales 1', 'spv sales 2']) || $user->isSpv1() || $user->isSpv2();

        // Start and end of the current month
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        $today = Carbon::now()->toDateString();

        if ($isSpv) {
            $kategoriSales = $request->input('kategori_sales', 'non_canvas');

            $achievedSalesQuery = Penjualan::where('batal', 0)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);

            $todaySalesQuery = Penjualan::where('batal', 0)
                ->whereDate('tanggal', $today);

            $todayVisitsQuery = PenjualanCheckin::whereDate('checkin', $today);

            $recentOrdersQuery = Penjualan::with(['pelanggan.wilayah', 'pelanggan.subWilayah', 'sales', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(5);

            if ($user->isSpv2()) {
                $assignedNiks = $user->assigned_sales_niks;
                $assignedUserIds = array_map('strval', $user->assigned_sales_ids);
                $allowedSales = array_merge($assignedNiks, $assignedUserIds);

                $achievedSalesQuery->where(function($q) use ($allowedSales) {
                    $q->whereIn('kode_sales', $allowedSales)->orWhereIn('id_user', $allowedSales);
                });
                $todaySalesQuery->where(function($q) use ($allowedSales) {
                    $q->whereIn('kode_sales', $allowedSales)->orWhereIn('id_user', $allowedSales);
                });
                $todayVisitsQuery->whereIn('kode_sales', $allowedSales);
                $recentOrdersQuery->where(function($q) use ($allowedSales) {
                    $q->whereIn('kode_sales', $allowedSales)->orWhereIn('id_user', $allowedSales);
                });
            }

            if ($kategoriSales === 'canvas') {
                $achievedSalesQuery->whereHas('sales', function ($q) {
                    $q->where('is_kanvas', 1);
                });
                $todaySalesQuery->whereHas('sales', function ($q) {
                    $q->where('is_kanvas', 1);
                });
                $todayVisitsQuery->whereHas('sales', function ($q) {
                    $q->where('is_kanvas', 1);
                });
                $recentOrdersQuery->whereHas('sales', function ($q) {
                    $q->where('is_kanvas', 1);
                });
            } elseif ($kategoriSales === 'non_canvas') {
                $achievedSalesQuery->where(function ($q) {
                    $q->whereHas('sales', function ($sq) {
                        $sq->where('is_kanvas', 0);
                    })->orWhereNull('kode_sales');
                });
                $todaySalesQuery->where(function ($q) {
                    $q->whereHas('sales', function ($sq) {
                        $sq->where('is_kanvas', 0);
                    })->orWhereNull('kode_sales');
                });
                $todayVisitsQuery->where(function ($q) {
                    $q->whereHas('sales', function ($sq) {
                        $sq->where('is_kanvas', 0);
                    })->orWhereNull('kode_sales');
                });
                $recentOrdersQuery->where(function ($q) {
                    $q->whereHas('sales', function ($sq) {
                        $sq->where('is_kanvas', 0);
                    })->orWhereNull('kode_sales');
                });
            }

            // Achieved sales of all sales this month
            $achievedSales = (float) $achievedSalesQuery->sum('grand_total');

            // Today's sales of all sales
            $todaySales = (float) $todaySalesQuery->sum('grand_total');

            // Today's visits count of all sales
            $todayVisitsCount = $todayVisitsQuery->count();

            // Recent orders of all sales
            $recentOrders = $recentOrdersQuery->get();
        } else {
            // 1. Achieved sales this month
            $achievedSales = (float) Penjualan::where('kode_sales', $nik)
                ->where('batal', 0)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->sum('grand_total');

            // 2. Today's sales
            $todaySales = (float) Penjualan::where('kode_sales', $nik)
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
            $custQuery = Pelanggan::where(function($q) {
                $q->whereNull('approve')->orWhere('approve', 0);
            });
            $limitQuery = AjuanLimitKredit::where('status', 'pending');
            $pembelianQuery = Pembelian::whereNull('tanggal_approve');

            if ($user->isSpv2()) {
                $assignedNiks = $user->assigned_sales_niks;
                $assignedUserIds = array_map('strval', $user->assigned_sales_ids);
                $allowedSales = array_merge($assignedNiks, $assignedUserIds);

                $custQuery->whereIn('kode_sales', $allowedSales);
                $limitQuery->whereIn('requested_by', $user->assigned_sales_ids);
                $pembelianQuery->whereIn('id_user', $allowedSales);
            }

            $pendingCustomersCount = $custQuery->count();
            $pendingLimitCount = $limitQuery->count();
            $pendingPembelianCount = $pembelianQuery->count();
        }

        // Target progress percentage
        $progressPercentage = 0;

        return view('mobile.dashboard', compact(
            'isSpv',
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
        $achievedSales = 0;
        $targetAmount = 0;
        $progressPercentage = 0;
        $totalOrdersCount = Penjualan::where('kode_sales', $user->nik)->count();
        $totalVisitsCount = PenjualanCheckin::where('kode_sales', $user->nik)->count();

        return view('mobile.profile', compact('user', 'achievedSales', 'targetAmount', 'progressPercentage', 'totalOrdersCount', 'totalVisitsCount'));
    }

    /**
     * Laporan Pencapaian Sales Mobile (untuk SPV Sales)
     */
    public function salesAchievement(Request $request)
    {
        $user = Auth::user();
        $role = strtolower($user->role ?? '');
        $isSpv = in_array($role, ['spv sales', 'spv sales 1', 'spv sales 2']) || $user->isSpv1() || $user->isSpv2();
        if (!$isSpv) {
            abort(403, 'Akses khusus SPV Sales.');
        }

        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));

        // Query sales users
        $salesListQuery = \App\Models\User::salesmen()->where('status', '1');

        if ($user->isSpv2()) {
            $assignedUserIds = $user->assigned_sales_ids;
            $salesListQuery->whereIn('id', $assignedUserIds);
        }

        $salesList = $salesListQuery->get();

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
        $user = Auth::user();
        $role = strtolower($user->role ?? '');
        $isSpv = in_array($role, ['spv sales', 'spv sales 1', 'spv sales 2']) || $user->isSpv1() || $user->isSpv2();
        if (!$isSpv) {
            abort(403, 'Akses khusus SPV Sales.');
        }

        $tanggal_mulai = $request->input('tanggal_mulai', date('Y-m-d'));
        $tanggal_akhir = $request->input('tanggal_akhir', date('Y-m-d'));
        $selected_sales = $request->input('kode_sales', '');

        $query = \App\Models\PenjualanCheckin::with(['sales', 'pelanggan.wilayah'])
            ->whereBetween('tanggal', [$tanggal_mulai, $tanggal_akhir]);

        if ($user->isSpv2()) {
            $assignedNiks = $user->assigned_sales_niks;
            if ($selected_sales !== '' && in_array($selected_sales, $assignedNiks)) {
                $query->where('kode_sales', $selected_sales);
            } else {
                $query->whereIn('kode_sales', $assignedNiks);
            }
        } elseif ($selected_sales !== '') {
            $query->where('kode_sales', $selected_sales);
        }

        $visits = $query->orderBy('checkin', 'desc')->paginate(20)->appends($request->query());

        $salesmenQuery = \App\Models\User::salesmen()->where('status', '1');
        if ($user->isSpv2()) {
            $salesmenQuery->whereIn('id', $user->assigned_sales_ids);
        }
        $salesmen = $salesmenQuery->orderBy('name')->get();

        return view('mobile.spv.sales_visits', compact('visits', 'salesmen', 'tanggal_mulai', 'tanggal_akhir', 'selected_sales'));
    }

    /**
     * Halaman List Pending Approval Pembelian untuk SPV Sales
     */
    public function pendingPembelianListSpv()
    {
        $user = Auth::user();
        $role = strtolower($user->role ?? '');
        $isSpv = in_array($role, ['spv sales', 'spv sales 1', 'spv sales 2']) || $user->isSpv1() || $user->isSpv2();
        if (!$isSpv) {
            abort(403, 'Akses khusus SPV Sales.');
        }

        $query = Pembelian::with(['supplier', 'details.barang'])
            ->whereNull('tanggal_approve');

        if ($user->isSpv2()) {
            $assignedNiks = $user->assigned_sales_niks;
            $assignedUserIds = array_map('strval', $user->assigned_sales_ids);
            $allowedSales = array_merge($assignedNiks, $assignedUserIds);

            $query->whereIn('id_user', $allowedSales);
        }

        $pendingPembelians = $query->orderBy('tanggal', 'desc')->get();

        return view('mobile.spv.pembelian_pending', compact('pendingPembelians'));
    }

    /**
     * Approve Pembelian via Mobile (SPV Sales)
     */
    public function approvePembelianSpv(Request $request, $no_faktur)
    {
        $user = Auth::user();
        $role = strtolower($user->role ?? '');
        $isSpv = in_array($role, ['spv sales', 'spv sales 1', 'spv sales 2']) || $user->isSpv1() || $user->isSpv2();
        if (!$isSpv) {
            abort(403, 'Akses khusus SPV Sales.');
        }

        $pembelian = Pembelian::findOrFail($no_faktur);

        if ($user->isSpv2()) {
            $assignedNiks = $user->assigned_sales_niks;
            $assignedUserIds = array_map('strval', $user->assigned_sales_ids);
            $allowedSales = array_merge($assignedNiks, $assignedUserIds);

            if (!in_array($pembelian->id_user, $allowedSales)) {
                return redirect()->route('mobile.spv.pembelian.pending')
                    ->with('error', 'Anda tidak memiliki wewenang untuk menyetujui pembelian dari sales ini.');
            }
        }

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
