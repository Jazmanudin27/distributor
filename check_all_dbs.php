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
echo "All Databases on 3306:\n";
foreach ($databases as $db) {
    echo " - $db\n";
}

foreach ($databases as $db) {
    if (in_array($db, ['information_schema', 'mysql', 'performance_schema', 'sys'])) continue;
    
    echo "\n=== Database: $db ===\n";
    try {
        config(['database.connections.mysql.database' => $db]);
        DB::purge('mysql');
        DB::reconnect('mysql');
        
        $tables = DB::select('SHOW TABLES');
        if (empty($tables)) {
            echo "No tables found.\n";
        } else {
            foreach ($tables as $t) {
                $prop = "Tables_in_" . $db;
                $tableName = $t->$prop;
                $count = DB::table($tableName)->count();
                echo " - $tableName ($count rows)\n";
            }
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
