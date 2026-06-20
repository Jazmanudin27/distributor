<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Merk;


class MerkController extends Controller
{
    public function index(Request $request)
    {
        $query = Merk::query();

        if ($request->filled('search')) {
            $query->where('nama_merk', 'like', '%' . $request->search . '%');
        }

        $merks = $query->paginate(10)->appends($request->query());
        return view('master.merk.index', compact('merks'));
    }

    public function create()
    {
        $item = new Merk();
        return view('master.merk.form', compact('item'));
    }

    public function store(Request $request)
    {
        \App\Models\Merk::create($request->validate([
            'nama_merk' => 'required'
        ]));
        
        return redirect()->route('merk.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        $item = Merk::findOrFail($id);
        return view('master.merk.form', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $row = Merk::findOrFail($id);

        $row->update($request->validate([
            'nama_merk' => 'required'
        ]));
        
        return redirect()->route('merk.index')->with('success', 'Data berhasil diubah');
    }

    public function destroy($id)
    {
        Merk::findOrFail($id)->delete();
        return redirect()->route('merk.index')->with('success', 'Data berhasil dihapus');
    }
}
