<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

config([
    'database.default' => 'sqlite',
    'database.connections.sqlite.database' => database_path('database.sqlite'),
]);
DB::purge();
DB::reconnect();

echo "PHP Current Local Time: " . date('Y-m-d H:i:s') . "\n";
echo "Carbon Today: " . Carbon::now()->toDateString() . "\n";

try {
    // List tables in SQLite
    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
    echo "\nTables in SQLite:\n";
    foreach ($tables as $t) {
        $name = $t->name;
        $count = DB::table($name)->count();
        echo " - $name ($count rows)\n";
    }
    
    $today = Carbon::now()->toDateString();
    
    // Check sales count
    if (Schema::hasTable('penjualan')) {
        $totalSales = DB::table('penjualan')->count();
        echo "\nTotal Penjualan: " . $totalSales . "\n";
        
        $salesToday = DB::table('penjualan')->where('tanggal', $today)->get();
        echo "Sales Today: " . $salesToday->count() . "\n";
        foreach ($salesToday as $s) {
            echo " - Faktur: {$s->no_faktur}, Tanggal: {$s->tanggal}, Batal: {$s->batal}, Total: {$s->grand_total}, Sales: {$s->kode_sales}\n";
        }
        
        echo "\n--- Latest 5 sales: ---\n";
        $latest = DB::table('penjualan')->orderBy('tanggal', 'desc')->limit(5)->get();
        foreach ($latest as $l) {
            echo " - Faktur: {$l->no_faktur}, Tanggal: {$l->tanggal}, Batal: {$l->batal}, Total: {$l->grand_total}, Sales: {$l->kode_sales}\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
