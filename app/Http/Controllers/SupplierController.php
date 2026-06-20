<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;


class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_supplier', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_supplier', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $suppliers = $query->paginate(10)->appends($request->query());
        return view('master.supplier.index', compact('suppliers'));
    }

    public function create()
    {
        $item = new Supplier();
        return view('master.supplier.form', compact('item'));
    }

    public function store(Request $request)
    {
        \App\Models\Supplier::create($request->validate([
            'kode_supplier' => 'required',
            'nama_supplier' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'email' => 'required',
            'status' => 'required'
        ]));
        
        return redirect()->route('supplier.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        $item = Supplier::findOrFail($id);
        return view('master.supplier.form', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $row = Supplier::findOrFail($id);

        $row->update($request->validate([
            'kode_supplier' => 'required',
            'nama_supplier' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'email' => 'required',
            'status' => 'required'
        ]));
        
        return redirect()->route('supplier.index')->with('success', 'Data berhasil diubah');
    }

    public function destroy($id)
    {
        Supplier::findOrFail($id)->delete();
        return redirect()->route('supplier.index')->with('success', 'Data berhasil dihapus');
    }
}
