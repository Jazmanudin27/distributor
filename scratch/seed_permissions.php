<?php
// Jalankan: php artisan tinker < seed_permissions.php
use Spatie\Permission\Models\Permission;

$permissions = [
    'view-ajuan_limit_kredit',
    'create-ajuan_limit_kredit',
    'approve-ajuan_limit_kredit',
];

foreach ($permissions as $p) {
    Permission::firstOrCreate(['name' => $p]);
    echo "Created: {$p}\n";
}
echo "Done!\n";
