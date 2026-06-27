<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.mysql.database' => 'information_schema']);
DB::purge('mysql');
DB::reconnect('mysql');

$databases = DB::table('SCHEMATA')->pluck('SCHEMA_NAME')->toArray();

echo "Scanning databases for 'barang' and 'diskon_strata' tables:\n";

foreach ($databases as $db) {
    if (in_array($db, ['information_schema', 'mysql', 'performance_schema', 'sys'])) continue;
    
    try {
        config(['database.connections.mysql.database' => $db]);
        DB::purge('mysql');
        DB::reconnect('mysql');
        
        $hasBarang = !empty(DB::select("SHOW TABLES LIKE 'barang'"));
        $hasDiskonStrata = !empty(DB::select("SHOW TABLES LIKE 'diskon_strata'"));
        
        if ($hasBarang || $hasDiskonStrata) {
            $barangCount = $hasBarang ? DB::table('barang')->count() : 'N/A';
            $diskonStrataCount = $hasDiskonStrata ? DB::table('diskon_strata')->count() : 'N/A';
            $usersCount = !empty(DB::select("SHOW TABLES LIKE 'users'")) ? DB::table('users')->count() : 0;
            
            echo " - [$db]: barang ($barangCount), diskon_strata ($diskonStrataCount), users ($usersCount)\n";
        }
    } catch (\Exception $e) {
        // Skip
    }
}
