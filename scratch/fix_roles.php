<?php
// Script dijalankan via: php artisan tinker < scratch/fix_roles.php
$users = App\Models\User::whereNotNull('role')->where('role', '!=', '')->get();
$mismatches = [];
foreach ($users as $u) {
    $spatie = $u->getRoleNames()->first();
    $col = $u->role;
    if ($col !== $spatie) {
        $mismatches[] = ['id' => $u->id, 'name' => $u->name, 'col_role' => $col, 'spatie_role' => $spatie ?? 'NONE'];
        // Auto-fix: sync role
        $u->syncRoles([$col]);
        echo "FIXED: {$u->name} (id={$u->id}) -> assigned role: {$col}\n";
    } else {
        echo "OK: {$u->name} (id={$u->id}) -> {$col}\n";
    }
}
echo "\nTotal mismatch fixed: " . count($mismatches) . "\n";
