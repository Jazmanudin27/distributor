<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wilayah;
use App\Models\Pelanggan;

class WilayahController extends Controller
{
    public function index(Request $request)
    {
        $query = Wilayah::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_wilayah', 'like', '%' . $search . '%')
                  ->orWhere('kode_wilayah', 'like', '%' . $search . '%');
            });
        }

        $wilayahs = $query->paginate(10)->appends($request->query());
        return view('master.wilayah.index', compact('wilayahs'));
    }

    public function create()
    {
        $item = new Wilayah();
        return view('master.wilayah.form', compact('item'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_wilayah' => 'nullable|integer|unique:wilayah,kode_wilayah',
            'nama_wilayah' => 'required|string|max:100',
        ]);

        if (empty($validated['kode_wilayah'])) {
            $validated['kode_wilayah'] = (int) (Wilayah::max('kode_wilayah') ?? 0) + 1;
        }

        Wilayah::create($validated);

        return redirect()->route('wilayah.index')->with('success', 'Data Wilayah berhasil ditambahkan');
    }

    public function edit($id)
    {
        $item = Wilayah::findOrFail($id);
        return view('master.wilayah.form', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $row = Wilayah::findOrFail($id);

        $validated = $request->validate([
            'kode_wilayah' => 'required|integer|unique:wilayah,kode_wilayah,' . $id . ',kode_wilayah',
            'nama_wilayah' => 'required|string|max:100',
        ]);

        $row->update($validated);

        return redirect()->route('wilayah.index')->with('success', 'Data Wilayah berhasil diubah');
    }

    public function destroy($id)
    {
        $row = Wilayah::findOrFail($id);

        $usedInPelanggan = Pelanggan::where('kode_wilayah', $id)->exists();
        if ($usedInPelanggan) {
            return redirect()->route('wilayah.index')->with('error', 'Data Wilayah tidak dapat dihapus karena sedang digunakan oleh data pelanggan.');
        }

        $row->delete();
        return redirect()->route('wilayah.index')->with('success', 'Data Wilayah berhasil dihapus');
    }

    public function toggleStatus($id)
    {
        $item = Wilayah::findOrFail($id);
        $item->status = ($item->status == 1) ? 0 : 1;
        $item->save();

        $statusText = $item->status == 1 ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Status Wilayah '{$item->nama_wilayah}' berhasil {$statusText}");
    }
}
