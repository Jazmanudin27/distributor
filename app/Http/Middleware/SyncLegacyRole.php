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

        if ($user && $user->roles->isEmpty() && !empty($user->role)) {
            $spatieRoleName = $this->roleMapping[$user->role] ?? $user->role;
            $roleObj = Role::where('name', $spatieRoleName)->first();

            if ($roleObj) {
                $user->syncRoles([$spatieRoleName]);
                // Reset cached permissions for this request
                $user->unsetRelation('roles');
                $user->unsetRelation('permissions');
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            }
        }

        return $next($request);
    }
}
