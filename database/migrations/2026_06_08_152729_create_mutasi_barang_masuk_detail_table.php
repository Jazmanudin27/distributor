<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('mutasi_barang_masuk_detail', function (Blueprint $table) {
            $table->id('id');
            $table->char('kode_transaksi', 50)->nullable();
            $table->char('no_faktur', 50)->nullable();
            $table->char('satuan_id', 50)->nullable();
            $table->integer('qty')->nullable();
            $table->integer('konversi')->nullable();
            $table->integer('qty_konversi')->nullable();
            $table->index(['kode_transaksi'], 'mutasi_barang_masuk_detail_idx_masuk_detail_kode_transaksi');
            $table->foreign('kode_transaksi', 'mutasi_barang_masuk_detail_ibfk_1')->references('kode_transaksi')->on('mutasi_barang_masuk')->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('mutasi_barang_masuk_detail');
        Schema::enableForeignKeyConstraints();
    }
};
