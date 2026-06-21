<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class SyncLegacyRole
{
    /**
     * Mapping nama role lama (kolom users.role) ke nama role Spatie.
     */
    protected array $roleMapping = [
        'Sales'       => 'Salesman',
        'SPV Sales'   => 'SPV Sales',
        'Admin'       => 'Admin',
        'Kasir'       => 'Kasir',
        'Owner'       => 'Owner',
        'Super Admin' => 'Super Admin',
        'Salesman'    => 'Salesman',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && !empty($user->role)) {
            $targetRoleName = $this->roleMapping[$user->role] ?? $user->role;
            $currentRoleName = $user->roles->first()?->name;

            // Koreksi jika role Spatie tidak ada atau tidak sesuai dengan kolom role
            if ($currentRoleName !== $targetRoleName) {
                $roleObj = Role::where('name', $targetRoleName)->first();

                if ($roleObj) {
                    $user->syncRoles([$targetRoleName]);
                    // Reload relasi agar perubahan terbaca di request ini
                    $user->unsetRelation('roles');
                    $user->unsetRelation('permissions');
                    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
                }
            }
        }

        return $next($request);
    }
}
