<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('wilayah', function (Blueprint $table) {
            $table->integer('kode_wilayah');
            $table->string('nama_wilayah', 100)->nullable();
            $table->primary(['kode_wilayah']);
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('wilayah');
        Schema::enableForeignKeyConstraints();
    }
};
