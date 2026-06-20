<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AjuanPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view-ajuan_limit_kredit',
            'create-ajuan_limit_kredit',
            'approve-ajuan_limit_kredit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
            $this->command->info("Permission ensured: {$permission}");
        }

        // Assign semua permission ke role Admin dan Super Admin
        $roleNames = ['Admin', 'Super Admin'];
        foreach ($roleNames as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
                $this->command->info("Permissions assigned to role: {$roleName}");
            }
        }

        // Kasir hanya bisa view dan create (tidak bisa approve)
        $kasir = Role::where('name', 'Kasir')->first();
        if ($kasir) {
            $kasir->givePermissionTo(['view-ajuan_limit_kredit', 'create-ajuan_limit_kredit']);
            $this->command->info("Permissions assigned to role: Kasir (view + create only)");
        }

        $this->command->info('Done!');
    }
}
