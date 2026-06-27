<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.mysql.database' => 'v4']);
DB::purge('mysql');
DB::reconnect('mysql');

echo "Like 'penjualan%' in v4:\n";
foreach (DB::select("SHOW TABLES LIKE 'penjualan%'") as $t) {
    echo " - " . reset($t) . "\n";
}

echo "\nLike 'retur%' in v4:\n";
foreach (DB::select("SHOW TABLES LIKE 'retur%'") as $t) {
    echo " - " . reset($t) . "\n";
}
