<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.mysql.database' => 'distributor']);
DB::purge('mysql');
DB::reconnect('mysql');

$users = DB::table('users')->get(['id', 'name', 'email', 'role', 'nik']);
echo "Users in 'distributor' database:\n";
foreach ($users as $u) {
    echo " - ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Role: {$u->role} | NIK: {$u->nik}\n";
}
