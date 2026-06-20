<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenjualanCheckin;
use Carbon\Carbon;

class SalesTrackingController extends Controller
{
    public function index(Request $request)
    {
        // Memeriksa izin akses (Super Admin atau user yang bisa melihat penjualan)
        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->can('view-penjualan')) {
            abort(403, 'Anda tidak memiliki hak akses ke pelacakan kunjungan.');
        }

        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());

        // Mengambil semua check-in yang memiliki koordinat latitude dan longitude pada tanggal terpilih
        $checkins = PenjualanCheckin::where('tanggal', $tanggal)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', '')
            ->where('longitude', '!=', '')
            ->with(['sales', 'pelanggan'])
            ->get();

        return view('sales_tracking.index', compact('checkins', 'tanggal'));
    }
}
