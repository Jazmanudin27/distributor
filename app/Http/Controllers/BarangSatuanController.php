<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangSatuan;
use App\Models\Barang;


class BarangSatuanController extends Controller
{
    public function index()
    {
        $barang_satuans = BarangSatuan::all();
        return view('barang_satuan.index', compact('barang_satuans'));
    }

    public function create()
    {
        $barangs = Barang::all();

        return view('barang_satuan.create', compact('barangs'));
    }

    public function store(Request $request)
    {

        BarangSatuan::create($request->validate([
            'kode_barang' => 'required',
            'satuan' => 'required',
            'isi' => 'required',
            'harga_pokok' => 'required',
            'harga_jual' => 'required'
        ]));

        $redirectTo = $request->input('redirect_to', route('barang_satuan.index'));
        return redirect($redirectTo)->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        $row = BarangSatuan::findOrFail($id);
        $barangs = Barang::all();

        return view('barang_satuan.edit', array_merge(compact('row'), compact('barangs')));
    }

    public function update(Request $request, $id)
    {
        $row = BarangSatuan::findOrFail($id);

        $row->update($request->validate([
            'kode_barang' => 'required',
            'satuan' => 'required',
            'isi' => 'required',
            'harga_pokok' => 'required',
            'harga_jual' => 'required'
        ]));

        $redirectTo = $request->input('redirect_to', route('barang_satuan.index'));
        return redirect($redirectTo)->with('success', 'Data berhasil diubah');
    }

    public function destroy($id)
    {
        BarangSatuan::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }
}
