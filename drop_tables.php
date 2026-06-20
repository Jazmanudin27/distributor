<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
Illuminate\Support\Facades\Schema::dropIfExists('model_has_permissions');
Illuminate\Support\Facades\Schema::dropIfExists('model_has_roles');
Illuminate\Support\Facades\Schema::dropIfExists('role_has_permissions');
Illuminate\Support\Facades\Schema::dropIfExists('permissions_roles');
Illuminate\Support\Facades\Schema::dropIfExists('permissions');
Illuminate\Support\Facades\Schema::dropIfExists('roles');
Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

echo "Tables dropped successfully.\n";
