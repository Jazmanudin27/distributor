<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Override database connection port to 3307
config(['database.connections.mysql.port' => '3307']);
DB::purge('mysql');
DB::reconnect('mysql');

$databases = ['mjap', 'distributor'];

foreach ($databases as $dbName) {
    echo "\n=== Database: $dbName (Port 3307) ===\n";
    try {
        config(['database.connections.mysql.database' => $dbName]);
        DB::purge('mysql');
        DB::reconnect('mysql');
        
        $tables = DB::select('SHOW TABLES');
        if (empty($tables)) {
            echo "No tables found.\n";
        } else {
            echo "Tables:\n";
            foreach ($tables as $table) {
                $prop = "Tables_in_" . $dbName;
                echo " - " . ($table->$prop ?? json_encode($table)) . "\n";
            }
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
