<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$targetItems = [
    'BRG001557', 'BRG001549', 'BRG001554', 'BRG001534', 
    'BRG001522', 'BRG001524', 'BRG001523', 'BRG001788'
];

$databases = ['mjap2', 'hajiaah', 'inventory', 'v4'];

foreach ($databases as $db) {
    try {
        config(['database.connections.mysql.database' => $db]);
        DB::purge('mysql');
        DB::reconnect('mysql');
        
        echo "\n=== Database: $db ===\n";
        foreach ($targetItems as $item) {
            $exists = DB::table('barang')->where('kode_barang', $item)->exists();
            echo " - $item: " . ($exists ? "EXISTS" : "MISSING") . "\n";
        }
    } catch (\Exception $e) {
        echo "Error on $db: " . $e->getMessage() . "\n";
    }
}
