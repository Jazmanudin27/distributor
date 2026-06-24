<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;


class UserController extends Controller
{
    public function index()
    {
        $userss = User::all();
        return view('users.index', compact('userss'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $kategoris = \App\Models\Kategori::orderBy('nama_kategori')->get();
        $merks = \App\Models\Merk::orderBy('nama_merk')->get();
        return view('users.create', compact('roles', 'kategoris', 'merks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'nullable|string',
            'role' => 'required',
            'nik' => 'required',
            'status' => 'required',
            'jenis_sales' => 'nullable|string|in:kategori,merk,semua',
            'jenis_barang' => 'nullable|array',
            'is_kanvas' => 'nullable|boolean',
        ]);
        
        if (isset($data['jenis_barang']) && is_array($data['jenis_barang'])) {
            $data['jenis_barang'] = implode(',', $data['jenis_barang']);
        } else {
            $data['jenis_barang'] = null;
        }

        $data['is_kanvas'] = $request->has('is_kanvas');
        $data['password'] = bcrypt($data['password'] ?? 'password');
        $user = \App\Models\User::create($data);

        // Assign role ke Spatie Permission
        if ($request->role) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route('users.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        $row = User::findOrFail($id);
        $roles = Role::orderBy('name')->get();
        $kategoris = \App\Models\Kategori::orderBy('nama_kategori')->get();
        $merks = \App\Models\Merk::orderBy('nama_merk')->get();

        return view('users.edit', compact('row', 'roles', 'kategoris', 'merks'));
    }

    public function update(Request $request, $id)
    {
        $row = User::findOrFail($id);
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'nullable|string',
            'role' => 'required',
            'nik' => 'required',
            'status' => 'required',
            'jenis_sales' => 'nullable|string|in:kategori,merk,semua',
            'jenis_barang' => 'nullable|array',
            'is_kanvas' => 'nullable|boolean',
        ]);

        if (isset($data['jenis_barang']) && is_array($data['jenis_barang'])) {
            $data['jenis_barang'] = implode(',', $data['jenis_barang']);
        } else {
            $data['jenis_barang'] = null;
        }

        $data['is_kanvas'] = $request->has('is_kanvas');

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = bcrypt($data['password']);
        }
        $row->update($data);

        // Sync role ke Spatie Permission
        if ($request->role) {
            $row->syncRoles([$request->role]);
        }

        return redirect()->route('users.index')->with('success', 'Data berhasil diubah');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return redirect()->route('users.index')->with('success', 'Data berhasil dihapus');
    }
}
