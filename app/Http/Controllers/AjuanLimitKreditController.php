<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AjuanLimitKredit;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AjuanLimitKreditController extends Controller
{
    /**
     * Daftar semua ajuan limit kredit
     */
    public function index(Request $request)
    {
        $query = AjuanLimitKredit::with(['pelanggan', 'requester', 'approver'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('pelanggan', function ($q) use ($request) {
                $q->where('nama_pelanggan', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_pelanggan', 'like', '%' . $request->search . '%');
            });
        }

        $ajuans = $query->paginate(15)->appends($request->query());

        $statusCounts = [
            'all'      => AjuanLimitKredit::count(),
            'pending'  => AjuanLimitKredit::where('status', 'pending')->count(),
            'approved' => AjuanLimitKredit::where('status', 'approved')->count(),
            'rejected' => AjuanLimitKredit::where('status', 'rejected')->count(),
        ];

        return view('ajuan_limit_kredit.index', compact('ajuans', 'statusCounts'));
    }

    /**
     * Form buat ajuan baru
     */
    public function create(Request $request)
    {
        $selectedPelanggan = null;
        if ($request->filled('kode_pelanggan')) {
            $selectedPelanggan = Pelanggan::find($request->kode_pelanggan);
        }

        $pelanggans = $selectedPelanggan ? collect([$selectedPelanggan]) : collect();

        return view('ajuan_limit_kredit.create', compact('pelanggans', 'selectedPelanggan'));
    }

    /**
     * Simpan ajuan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_pelanggan' => 'required|exists:pelanggan,kode_pelanggan',
            'limit_baru'     => 'required|numeric|min:0',
            'alasan'         => 'required|string|max:1000',
        ], [
            'kode_pelanggan.required' => 'Pelanggan wajib dipilih.',
            'kode_pelanggan.exists'   => 'Pelanggan tidak ditemukan.',
            'limit_baru.required'     => 'Limit baru wajib diisi.',
            'limit_baru.numeric'      => 'Limit baru harus berupa angka.',
            'limit_baru.min'          => 'Limit baru tidak boleh negatif.',
            'alasan.required'         => 'Alasan pengajuan wajib diisi.',
        ]);

        $pelanggan = Pelanggan::findOrFail($request->kode_pelanggan);

        // Cek apakah sudah ada ajuan pending untuk pelanggan ini
        $existingPending = AjuanLimitKredit::where('kode_pelanggan', $request->kode_pelanggan)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return back()->withInput()->with('error', 'Pelanggan ini sudah memiliki ajuan limit yang sedang menunggu persetujuan.');
        }

        AjuanLimitKredit::create([
            'kode_pelanggan' => $request->kode_pelanggan,
            'limit_lama'     => $pelanggan->limit_pelanggan,
            'limit_baru'     => $request->limit_baru,
            'alasan'         => $request->alasan,
            'status'         => 'pending',
            'requested_by'   => Auth::id(),
        ]);

        return redirect()->route('ajuan-limit-kredit.index')
            ->with('success', 'Ajuan limit kredit berhasil disubmit.');
    }

    /**
     * Detail ajuan
     */
    public function show($id)
    {
        $ajuan = AjuanLimitKredit::with(['pelanggan', 'requester', 'approver'])->findOrFail($id);
        return view('ajuan_limit_kredit.show', compact('ajuan'));
    }

    /**
     * Setujui ajuan dan update limit pelanggan
     */
    public function approve(Request $request, $id)
    {
        $ajuan = AjuanLimitKredit::findOrFail($id);

        if (!$ajuan->isPending()) {
            return back()->with('error', 'Ajuan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($ajuan, $request) {
            // Update limit pelanggan
            $ajuan->pelanggan->update([
                'limit_pelanggan' => $ajuan->limit_baru,
            ]);

            // Update status ajuan
            $ajuan->update([
                'status'       => 'approved',
                'catatan_admin' => $request->catatan_admin,
                'approved_by'  => Auth::id(),
                'approved_at'  => now(),
            ]);
        });

        return redirect()->route('ajuan-limit-kredit.index')
            ->with('success', 'Ajuan limit kredit telah disetujui. Limit pelanggan berhasil diperbarui.');
    }

    /**
     * Tolak ajuan
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan_admin' => 'required|string|max:500',
        ], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
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

        return redirect()->route('ajuan-limit-kredit.index')
            ->with('success', 'Ajuan limit kredit telah ditolak.');
    }
}
