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

echo "Scanning all databases for recent sales in 2026:\n";

foreach ($databases as $db) {
    if (in_array($db, ['information_schema', 'mysql', 'performance_schema', 'sys'])) continue;
    
    try {
        config(['database.connections.mysql.database' => $db]);
        DB::purge('mysql');
        DB::reconnect('mysql');
        
        // Find a table like penjualan
        $tables = DB::select("SHOW TABLES");
        $penjualanTable = null;
        foreach ($tables as $t) {
            $tableName = reset($t);
            if (in_array(strtolower($tableName), ['penjualan', 't_penjualan', 'tb_penjualan'])) {
                $penjualanTable = $tableName;
                break;
            }
        }
        
        if ($penjualanTable) {
            $count = DB::table($penjualanTable)->count();
            if ($count > 0) {
                // Get latest date
                // Check if date column exists
                $columns = DB::getSchemaBuilder()->getColumnListing($penjualanTable);
                $dateCol = null;
                foreach ($columns as $c) {
                    if (in_array(strtolower($c), ['tanggal', 'tgl', 'date', 'created_at'])) {
                        $dateCol = $c;
                        break;
                    }
                }
                
                $latestDate = 'N/A';
                if ($dateCol) {
                    $latestRow = DB::table($penjualanTable)->orderBy($dateCol, 'desc')->first();
                    $latestDate = $latestRow->$dateCol ?? 'N/A';
                }
                
                echo " - [$db] Table: $penjualanTable | Count: $count | Latest Date: $latestDate\n";
            }
        }
    } catch (\Exception $e) {
        // Skip
    }
}
