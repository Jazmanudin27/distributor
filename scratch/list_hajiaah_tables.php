<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.mysql.database' => 'hajiaah']);
DB::purge('mysql');
DB::reconnect('mysql');

$tables = DB::select("SHOW TABLES");
foreach ($tables as $t) {
    echo reset($t) . "\n";
}
