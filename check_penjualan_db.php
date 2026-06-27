<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$databases = ['ndr', 'v4', 'inventory', 'hajiaah', 'barayasnack'];

foreach ($databases as $db) {
    echo "\n=== Database: $db ===\n";
    try {
        config(['database.connections.mysql.database' => $db]);
        DB::purge('mysql');
        DB::reconnect('mysql');
        
        $tables = ['pelanggan', 'barang', 'supplier', 'penjualan', 'retur_penjualan', 'kunjungan_checkin', 'ajuan_limit_kredit'];
        foreach ($tables as $t) {
            $check = DB::select("SHOW TABLES LIKE '$t'");
            if (!empty($check)) {
                $count = DB::table($t)->count();
                echo " - $t: $count rows\n";
            } else {
                echo " - $t: DOES NOT EXIST\n";
            }
        }
    } catch (\Exception $e) {
        echo "Error connecting: " . $e->getMessage() . "\n";
    }
}
