<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubWilayah;
use App\Models\Pelanggan;

class SubWilayahController extends Controller
{
    public function index(Request $request)
    {
        $query = SubWilayah::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_wilayah', 'like', '%' . $search . '%')
                  ->orWhere('kode_wilayah', 'like', '%' . $search . '%');
            });
        }

        $subWilayahs = $query->paginate(10)->appends($request->query());
        return view('master.sub_wilayah.index', compact('subWilayahs'));
    }

    public function create()
    {
        $item = new SubWilayah();
        return view('master.sub_wilayah.form', compact('item'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_wilayah' => 'nullable|integer|unique:sub_wilayah,kode_wilayah',
            'nama_wilayah' => 'required|string|max:100',
        ]);

        if (empty($validated['kode_wilayah'])) {
            $validated['kode_wilayah'] = (int) (SubWilayah::max('kode_wilayah') ?? 0) + 1;
        }

        SubWilayah::create($validated);

        return redirect()->route('sub-wilayah.index')->with('success', 'Data Sub Wilayah berhasil ditambahkan');
    }

    public function edit($id)
    {
        $item = SubWilayah::findOrFail($id);
        return view('master.sub_wilayah.form', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $row = SubWilayah::findOrFail($id);

        $validated = $request->validate([
            'kode_wilayah' => 'required|integer|unique:sub_wilayah,kode_wilayah,' . $id . ',kode_wilayah',
            'nama_wilayah' => 'required|string|max:100',
        ]);

        $row->update($validated);

        return redirect()->route('sub-wilayah.index')->with('success', 'Data Sub Wilayah berhasil diubah');
    }

    public function destroy($id)
    {
        $row = SubWilayah::findOrFail($id);

        $usedInPelanggan = Pelanggan::where('sub_wilayah', $id)->exists();
        if ($usedInPelanggan) {
            return redirect()->route('sub-wilayah.index')->with('error', 'Data Sub Wilayah tidak dapat dihapus karena sedang digunakan oleh data pelanggan.');
        }

        $row->delete();
        return redirect()->route('sub-wilayah.index')->with('success', 'Data Sub Wilayah berhasil dihapus');
    }

    public function toggleStatus($id)
    {
        $item = SubWilayah::findOrFail($id);
        $item->status = ($item->status == 1) ? 0 : 1;
        $item->save();

        $statusText = $item->status == 1 ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Status Sub Wilayah '{$item->nama_wilayah}' berhasil {$statusText}");
    }
}
