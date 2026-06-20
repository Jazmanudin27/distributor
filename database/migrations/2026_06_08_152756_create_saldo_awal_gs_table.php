<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('saldo_awal_gs', function (Blueprint $table) {
            $table->id('id');
            $table->string('kode_barang', 50)->nullable();
            $table->integer('bulan')->nullable();
            $table->integer('tahun')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('qty')->nullable();
            $table->text('keterangan')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unique(['kode_barang', 'bulan', 'tahun'], 'saldo_awal_gs_uniq_saldo_barang_bulantahun');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('saldo_awal_gs');
        Schema::enableForeignKeyConstraints();
    }
};
