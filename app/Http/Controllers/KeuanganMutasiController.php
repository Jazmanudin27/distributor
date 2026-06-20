<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KeuanganMutasi;
use App\Models\Bank;
use Illuminate\Support\Facades\Auth;

class KeuanganMutasiController extends Controller
{
    public function index(Request $request)
    {
        $query = KeuanganMutasi::with(['bank', 'user']);

        if ($request->filled('search')) {
            $query->where('keterangan', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('kode_bank')) {
            $query->where('kode_bank', $request->kode_bank);
        }

        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal', '<=', $request->tanggal_akhir);
        }

        // Calculate metrics on the filtered dataset
        $totalDebet = (float)(clone $query)->where('tipe', 'debet')->sum('jumlah');
        $totalKredit = (float)(clone $query)->where('tipe', 'kredit')->sum('jumlah');
        $saldo = $totalDebet - $totalKredit;

        $items = $query->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->appends($request->query());

        $banks = Bank::orderBy('nama_bank', 'asc')->get();

        return view('kas_bank.index', compact('items', 'banks', 'totalDebet', 'totalKredit', 'saldo'));
    }

    public function create()
    {
        $item = new KeuanganMutasi();
        $item->tanggal = date('Y-m-d');
        $banks = Bank::orderBy('nama_bank', 'asc')->get();
        return view('kas_bank.form', compact('item', 'banks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'kode_bank' => 'required|exists:bank,id',
            'tipe' => 'required|in:debet,kredit',
            'jumlah' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string',
        ]);

        $data['id_user'] = Auth::id();

        KeuanganMutasi::create($data);

        return redirect()->route('kas-bank.index')->with('success', 'Transaksi kas & bank berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = KeuanganMutasi::findOrFail($id);
        $banks = Bank::orderBy('nama_bank', 'asc')->get();
        return view('kas_bank.form', compact('item', 'banks'));
    }

    public function update(Request $request, $id)
    {
        $item = KeuanganMutasi::findOrFail($id);

        $data = $request->validate([
            'tanggal' => 'required|date',
            'kode_bank' => 'required|exists:bank,id',
            'tipe' => 'required|in:debet,kredit',
            'jumlah' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string',
        ]);

        $item->update($data);

        return redirect()->route('kas-bank.index')->with('success', 'Transaksi kas & bank berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = KeuanganMutasi::findOrFail($id);
        $item->delete();

        return redirect()->route('kas-bank.index')->with('success', 'Transaksi kas & bank berhasil dihapus.');
    }
}
