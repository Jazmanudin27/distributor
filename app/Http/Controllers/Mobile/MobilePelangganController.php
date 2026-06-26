<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Wilayah;
use App\Models\SubWilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MobilePelangganController extends Controller
{
    /**
     * Show registration form for new customer
     */
    public function create()
    {
        $wilayahs = Wilayah::orderBy('nama_wilayah')->get();
        $subWilayahs = SubWilayah::orderBy('nama_wilayah')->get();

        return view('mobile.pelanggan.create', compact('wilayahs', 'subWilayahs'));
    }

    /**
     * Store new customer pending approval
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan'   => 'required|string|max:100',
            'alamat_pelanggan' => 'required|string|max:150',
            'alamat_toko'      => 'nullable|string|max:150',
            'no_hp_pelanggan'  => 'required|string|max:30',
            'kode_wilayah'     => 'required|exists:wilayah,kode_wilayah',
            'sub_wilayah'      => 'required|exists:sub_wilayah,kode_wilayah',
            'metode_bayar'     => 'required|in:Cash,Kredit,Transfer',
            'latitude'         => 'nullable|string|max:100',
            'longitude'        => 'nullable|string|max:100',
            'foto'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'foto_ktp'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        // Auto-generate kode_pelanggan: PLG{y}{5-digit-sequence}
        $prefix = 'PLG' . date('y');
        
        // Find the maximum sequence number for this prefix
        $last = Pelanggan::where('kode_pelanggan', 'like', $prefix . '%')
            ->orderBy('kode_pelanggan', 'desc')
            ->first();

        $nextNum = 1;
        if ($last) {
            $lastNum = intval(substr($last->kode_pelanggan, 5));
            $nextNum = $lastNum + 1;
        }
        $kodePelanggan = $prefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

        // Process file uploads
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_foto_' . uniqid() . '.' . $file->getClientOriginalExtension();
            if (!file_exists(public_path('storage/uploads/pelanggan'))) {
                mkdir(public_path('storage/uploads/pelanggan'), 0777, true);
            }
            $file->move(public_path('storage/uploads/pelanggan'), $filename);
            $fotoPath = 'storage/uploads/pelanggan/' . $filename;
        }

        $fotoKtpPath = null;
        if ($request->hasFile('foto_ktp')) {
            $file = $request->file('foto_ktp');
            $filename = time() . '_ktp_' . uniqid() . '.' . $file->getClientOriginalExtension();
            if (!file_exists(public_path('storage/uploads/pelanggan'))) {
                mkdir(public_path('storage/uploads/pelanggan'), 0777, true);
            }
            $file->move(public_path('storage/uploads/pelanggan'), $filename);
            $fotoKtpPath = 'storage/uploads/pelanggan/' . $filename;
        }

        $user = Auth::user();
        $isCanvas = (bool)($user->is_kanvas ?? false);

        Pelanggan::create([
            'kode_pelanggan'   => $kodePelanggan,
            'nama_pelanggan'   => $request->nama_pelanggan,
            'alamat_pelanggan' => $request->alamat_pelanggan,
            'alamat_toko'      => $request->alamat_toko ?? $request->alamat_pelanggan,
            'tanggal_register' => now()->toDateString(),
            'no_hp_pelanggan'  => $request->no_hp_pelanggan,
            'metode_bayar'     => $request->metode_bayar,
            'limit_pelanggan'  => 200000, // Default to 200 rb limit
            'ljt'              => 30, // Default to 30 days LJT
            'latitude'         => $request->filled('latitude') ? $request->latitude : null,
            'longitude'        => $request->filled('longitude') ? $request->longitude : null,
            'foto'             => $fotoPath,
            'foto_ktp'         => $fotoKtpPath,
            'kode_wilayah'     => $request->kode_wilayah,
            'sub_wilayah'      => $request->sub_wilayah,
            'status'           => 1, // Status active
            'approve'          => $isCanvas ? 1 : 0, // Auto-approved if sales canvas
            'kode_sales'       => $isCanvas ? $user->nik : null, // Set sales code for canvas sales
            'jenis_pelanggan'  => $isCanvas ? '1' : '0', // Unlimited limit for canvas sales, regular by default
        ]);

        $message = $isCanvas 
            ? 'Pelanggan baru berhasil didaftarkan dan siap dikunjungi.'
            : 'Pelanggan baru berhasil didaftarkan dan sedang menunggu persetujuan (approval).';

        return redirect()->route('mobile.kunjungan.index')->with('success', $message);
    }

    /**
     * List pending customer registrations for SPV Sales
     */
    public function pendingListSpv()
    {
        // Only SPV Sales can access
        if (strtolower(Auth::user()->role) !== 'spv sales') {
            abort(403, 'Hanya SPV Sales yang memiliki akses ke persetujuan pelanggan.');
        }

        $pendingCustomers = Pelanggan::with(['wilayah', 'subWilayah'])
            ->where(function($q) {
                $q->whereNull('approve')->orWhere('approve', 0);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mobile.pelanggan.pending_spv', compact('pendingCustomers'));
    }

    /**
     * SPV Sales: Approve customer
     */
    public function approveSpv($kode_pelanggan)
    {
        if (strtolower(Auth::user()->role) !== 'spv sales') {
            abort(403, 'Akses ditolak.');
        }

        $pelanggan = Pelanggan::findOrFail($kode_pelanggan);
        $pelanggan->update([
            'approve' => 1,
            'status' => 1,
        ]);

        return redirect()->route('mobile.spv.pelanggan.pending')->with('success', "Pelanggan '{$pelanggan->nama_pelanggan}' berhasil disetujui!");
    }

    /**
     * SPV Sales: Reject customer
     */
    public function rejectSpv($kode_pelanggan)
    {
        if (strtolower(Auth::user()->role) !== 'spv sales') {
            abort(403, 'Akses ditolak.');
        }

        $pelanggan = Pelanggan::findOrFail($kode_pelanggan);
        
        // Delete or set approve = 2 (Rejected). Setting to 2 is better.
        $pelanggan->update([
            'approve' => 2,
        ]);

        return redirect()->route('mobile.spv.pelanggan.pending')->with('warning', "Pendaftaran pelanggan '{$pelanggan->nama_pelanggan}' ditolak.");
    }
}
