<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SyncUserRoles extends Command
{
    protected $signature = 'users:sync-roles';
    protected $description = 'Sync role kolom users ke Spatie Permission model_has_roles';

    public function handle()
    {
        $users = User::whereNotNull('role')->where('role', '!=', '')->get();
        $validRoles = Role::pluck('name')->toArray();
        $fixed = 0;

        foreach ($users as $user) {
            $colRole    = $user->role;
            $spatieRole = $user->getRoleNames()->first();

            // Skip jika role di kolom tidak valid di Spatie
            if (!in_array($colRole, $validRoles)) {
                $this->warn("SKIP: {$user->name} (id={$user->id}) -> role '{$colRole}' tidak ada di tabel roles Spatie");
                continue;
            }

            if ($colRole !== $spatieRole) {
                $user->syncRoles([$colRole]);
                $this->line("FIXED: {$user->name} (id={$user->id}) -> assigned role: {$colRole}");
                $fixed++;
            } else {
                $this->line("OK: {$user->name} (id={$user->id}) -> {$colRole}");
            }
        }

        $this->info("\nSelesai. Total diperbaiki: {$fixed} user.");
        return 0;
    }
}
