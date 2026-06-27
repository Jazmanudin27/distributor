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

echo "Searching for database with 'users' table containing 'nik' column:\n";

foreach ($databases as $db) {
    if (in_array($db, ['information_schema', 'mysql', 'performance_schema', 'sys'])) continue;
    
    try {
        config(['database.connections.mysql.database' => $db]);
        DB::purge('mysql');
        DB::reconnect('mysql');
        
        $columns = DB::getSchemaBuilder()->getColumnListing('users');
        if (in_array('nik', $columns)) {
            $count = DB::table('users')->count();
            echo " - [$db] has 'users' with 'nik' column! Row count: $count\n";
            $first = DB::table('users')->first();
            echo "   First user: " . json_encode($first) . "\n";
        }
    } catch (\Exception $e) {
        // Skip
    }
}
