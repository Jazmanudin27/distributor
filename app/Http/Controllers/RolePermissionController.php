<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::where('name', '!=', 'Super Admin')
            ->with('permissions')
            ->get();
        $allPermissions = Permission::orderBy('name')->get();

        return view('roles.index', compact('roles', 'allPermissions'));
    }

    public function updatePermissions(Request $request)
    {
        $data = $request->input('permissions', []);
        
        $roles = Role::where('name', '!=', 'Super Admin')->get();
        
        DB::beginTransaction();
        try {
            foreach ($roles as $role) {
                // $data[$role->id] will contain an array of permission IDs if checked
                $permissionIds = $data[$role->id] ?? [];
                
                // Get permission names from IDs
                $permissions = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();
                
                // Sync permissions for this role
                $role->syncPermissions($permissions);
            }
            DB::commit();
            return redirect()->route('roles.index')->with('success', 'Hak akses berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
        ], [
            'name.required' => 'Nama permission wajib diisi!',
        ]);

        $permissionName = strtolower(trim($request->name));

        if (Permission::where('name', $permissionName)->exists()) {
            return redirect()->back()->withInput()->with('error', 'Permission ' . $permissionName . ' sudah terdaftar.');
        }

        Permission::create(['name' => $permissionName]);

        // Auto-assign to Super Admin
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissionName);
        }

        return redirect()->route('roles.index')->with('success', 'Permission ' . $permissionName . ' berhasil ditambahkan!');
    }

    public function destroyPermission(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('roles.index')->with('success', 'Permission berhasil dihapus!');
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name'
        ]);

        Role::create(['name' => $request->name]);

        return redirect()->route('roles.index')->with('success', 'Role ' . $request->name . ' berhasil ditambahkan!');
    }

    public function destroyRole(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return redirect()->back()->with('error', 'Role Super Admin tidak boleh dihapus.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus!');
    }
}
