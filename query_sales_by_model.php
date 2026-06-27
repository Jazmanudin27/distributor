<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;

config(['database.connections.mysql.database' => 'distributor']);
DB::purge('mysql');
DB::reconnect('mysql');

try {
    echo "Default Connection Name: " . DB::getDefaultConnection() . "\n";
    echo "Database Name: " . DB::connection()->getDatabaseName() . "\n";
    
    echo "Penjualan count: " . Penjualan::count() . "\n";
    echo "PenjualanDetail count: " . PenjualanDetail::count() . "\n";
    echo "User count: " . User::count() . "\n";
    
    $latest = Penjualan::orderBy('tanggal', 'desc')->limit(5)->get(['no_faktur', 'tanggal', 'kode_sales']);
    echo "Latest 5 sales:\n";
    foreach ($latest as $l) {
        echo " - Faktur: {$l->no_faktur}, Tanggal: {$l->tanggal}, Sales: {$l->kode_sales}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
