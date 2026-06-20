<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('mutasi_barang_keluar_detail', function (Blueprint $table) {
            $table->id('id');
            $table->char('kode_transaksi', 50)->nullable();
            $table->bigInteger('satuan_id')->nullable();
            $table->integer('qty')->nullable();
            $table->integer('konversi')->nullable()->default(1);
            $table->integer('qty_konversi')->nullable()->default(1);
            $table->index(['kode_transaksi'], 'mutasi_barang_keluar_detail_idx_keluar_detail_kode_transaksi');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('mutasi_barang_keluar_detail');
        Schema::enableForeignKeyConstraints();
    }
};
