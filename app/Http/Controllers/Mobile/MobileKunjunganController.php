<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\PenjualanCheckin;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileKunjunganController extends Controller
{
    public function index()
    {
        $nik = Auth::user()->nik;

        // Check if there is an active check-in session (checkout is null)
        $activeCheckin = PenjualanCheckin::with('pelanggan')
            ->where('kode_sales', $nik)
            ->whereNull('checkout')
            ->first();

        $lastOrders = collect();
        $unpaidInvoices = collect();
        if ($activeCheckin) {
            $lastOrders = \App\Models\Penjualan::with(['details.barang', 'details.barangSatuan', 'pembayarans', 'pelanggan'])
                ->where('kode_pelanggan', $activeCheckin->kode_pelanggan)
                ->where('batal', 0)
                ->orderBy('tanggal', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $unpaidInvoices = \App\Models\Penjualan::with(['pembayarans', 'pelanggan'])
                ->where('kode_pelanggan', $activeCheckin->kode_pelanggan)
                ->where('batal', 0)
                ->whereRaw('(COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran WHERE penjualan_pembayaran.no_faktur = penjualan.no_faktur), 0) + COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_transfer WHERE penjualan_pembayaran_transfer.no_faktur = penjualan.no_faktur), 0) + COALESCE((SELECT SUM(jumlah) FROM penjualan_pembayaran_giro WHERE penjualan_pembayaran_giro.no_faktur = penjualan.no_faktur), 0)) < grand_total')
                ->orderBy('tanggal', 'asc')
                ->get();
        }

        // Get recent visits today
        $todayVisits = PenjualanCheckin::with('pelanggan')
            ->where('kode_sales', $nik)
            ->whereDate('checkin', now()->toDateString())
            ->whereNotNull('checkout')
            ->orderBy('checkout', 'desc')
            ->get();

        $wilayahs = \App\Models\Wilayah::orderBy('nama_wilayah')->get();
        return view('mobile.kunjungan', compact('activeCheckin', 'todayVisits', 'lastOrders', 'unpaidInvoices', 'wilayahs'));
    }

    public function checkin(Request $request)
    {
        $request->validate([
            'kode_pelanggan' => 'required|exists:pelanggan,kode_pelanggan',
        ]);

        $nik = Auth::user()->nik;

        // Check if there is already an active check-in
        $hasActive = PenjualanCheckin::where('kode_sales', $nik)
            ->whereNull('checkout')
            ->exists();

        if ($hasActive) {
            return redirect()->back()->with('error', 'Anda masih memiliki kunjungan aktif. Harap check-out terlebih dahulu.');
        }

        // Create check-in log
        PenjualanCheckin::create([
            'kode_sales' => $nik,
            'kode_pelanggan' => $request->kode_pelanggan,
            'tanggal' => now()->toDateString(),
            'checkin' => now(),
            'latitude' => $request->latitude ?? null,
            'longitude' => $request->longitude ?? null,
        ]);

        return redirect()->route('mobile.kunjungan.index')->with('success', 'Check-in berhasil! Kunjungan Anda sedang dicatat.');
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'catatan' => 'required|string|min:3',
        ]);

        $nik = Auth::user()->nik;

        // Find active check-in
        $active = PenjualanCheckin::where('kode_sales', $nik)
            ->whereNull('checkout')
            ->first();

        if (!$active) {
            return redirect()->back()->with('error', 'Kunjungan aktif tidak ditemukan.');
        }

        // Update checkout
        $active->update([
            'checkout' => now(),
            'catatan' => $request->catatan,
            'latitude' => $request->latitude ?? $active->latitude,
            'longitude' => $request->longitude ?? $active->longitude,
        ]);

        return redirect()->route('mobile.kunjungan.index')->with('success', 'Check-out berhasil! Laporan kunjungan telah disimpan.');
    }
}
