<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AjuanLimitKredit;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MobileAjuanLimitController extends Controller
{
    public function index(Request $request)
    {
        $role = strtolower(Auth::user()->role ?? '');
        $isSpv = ($role === 'spv sales');

        $ajuans = AjuanLimitKredit::with(['pelanggan'])
            ->where('requested_by', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'page_my');

        $pendingAjuans = collect();
        if ($isSpv) {
            $pendingAjuans = AjuanLimitKredit::with(['pelanggan', 'requester'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('mobile.limit_kredit.index', compact('ajuans', 'isSpv', 'pendingAjuans'));
    }

    public function create(Request $request)
    {
        $selectedPelanggan = null;
        $kodePelanggan = old('kode_pelanggan') ?? $request->kode_pelanggan;
        if ($kodePelanggan) {
            $selectedPelanggan = Pelanggan::where('kode_pelanggan', $kodePelanggan)->first();
        }

        $wilayahs = \App\Models\Wilayah::orderBy('nama_wilayah')->get();
        return view('mobile.limit_kredit.create', compact('selectedPelanggan', 'wilayahs'));
    }

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

        $pelanggan = Pelanggan::where('kode_pelanggan', $request->kode_pelanggan)->firstOrFail();

        // Check for existing pending request
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

        return redirect()->route('mobile.limit-kredit.index')
            ->with('success', 'Ajuan limit kredit berhasil disubmit.');
    }

    public function approveSpv(Request $request, $id)
    {
        if (strtolower(Auth::user()->role) !== 'spv sales') {
            abort(403, 'Akses ditolak.');
        }

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

        return redirect()->route('mobile.limit-kredit.index')
            ->with('success', 'Ajuan limit kredit pelanggan berhasil disetujui.');
    }

    public function rejectSpv(Request $request, $id)
    {
        if (strtolower(Auth::user()->role) !== 'spv sales') {
            abort(403, 'Akses ditolak.');
        }

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

        return redirect()->route('mobile.limit-kredit.index')
            ->with('success', 'Ajuan limit kredit pelanggan telah ditolak.');
    }
}
