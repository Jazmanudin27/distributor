<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.mysql.database' => 'project']);
DB::purge('mysql');
DB::reconnect('mysql');

$tables = DB::select('SHOW TABLES');
echo "Tables in project database:\n";
foreach ($tables as $t) {
    $tableName = reset($t);
    $count = DB::table($tableName)->count();
    echo " - $tableName ($count rows)\n";
}
