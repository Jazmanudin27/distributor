<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('wilayah') && !Schema::hasColumn('wilayah', 'status')) {
            Schema::table('wilayah', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->after('nama_wilayah');
            });
        }

        if (Schema::hasTable('sub_wilayah') && !Schema::hasColumn('sub_wilayah', 'status')) {
            Schema::table('sub_wilayah', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->after('nama_wilayah');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('wilayah') && Schema::hasColumn('wilayah', 'status')) {
            Schema::table('wilayah', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasTable('sub_wilayah') && Schema::hasColumn('sub_wilayah', 'status')) {
            Schema::table('sub_wilayah', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
