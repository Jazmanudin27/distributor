<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.mysql.database' => 'distributor']);
DB::purge('mysql');
DB::reconnect('mysql');

$columns = DB::getSchemaBuilder()->getColumnListing('users');
echo "Columns in 'users' table:\n";
print_r($columns);

$firstRow = DB::table('users')->first();
echo "\nFirst row: \n";
print_r($firstRow);
