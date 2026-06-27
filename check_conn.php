<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Default Connection: " . config('database.default') . "\n";
    $config = config('database.connections.' . config('database.default'));
    echo "Config host: " . ($config['host'] ?? '-') . "\n";
    echo "Config port: " . ($config['port'] ?? '-') . "\n";
    echo "Config database: " . ($config['database'] ?? '-') . "\n";
    echo "Config username: " . ($config['username'] ?? '-') . "\n";
    
    echo "Actual DB name: " . DB::connection()->getDatabaseName() . "\n";
    
    $users = DB::table('users')->get(['id', 'name', 'email']);
    echo "Users count: " . $users->count() . "\n";
    foreach ($users as $u) {
        echo " - {$u->name} ({$u->email})\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
