<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('saldo_awal_bs', function (Blueprint $table) {
            $table->id('id');
            $table->string('kode_barang', 50)->nullable();
            $table->date('tanggal')->nullable();
            $table->bigInteger('bulan')->nullable();
            $table->bigInteger('tahun')->nullable();
            $table->integer('qty')->nullable();
            $table->text('keterangan')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unique(['kode_barang', 'bulan', 'tahun'], 'saldo_awal_bs_uniq_saldo_barang_bulantahun');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('saldo_awal_bs');
        Schema::enableForeignKeyConstraints();
    }
};
