<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;


class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $query = Kategori::query();

        if ($request->filled('search')) {
            $query->where('nama_kategori', 'like', '%' . $request->search . '%');
        }

        $kategoris = $query->paginate(10)->appends($request->query());
        return view('master.kategori.index', compact('kategoris'));
    }

    public function create()
    {
        $item = new Kategori();
        return view('master.kategori.form', compact('item'));
    }

    public function store(Request $request)
    {
        \App\Models\Kategori::create($request->validate([
            'nama_kategori' => 'required'
        ]));
        
        return redirect()->route('kategori.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        $item = Kategori::findOrFail($id);
        return view('master.kategori.form', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $row = Kategori::findOrFail($id);

        $row->update($request->validate([
            'nama_kategori' => 'required'
        ]));
        
        return redirect()->route('kategori.index')->with('success', 'Data berhasil diubah');
    }

    public function destroy($id)
    {
        Kategori::findOrFail($id)->delete();
        return redirect()->route('kategori.index')->with('success', 'Data berhasil dihapus');
    }
}
