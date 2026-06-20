<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('pembelian_detail', function (Blueprint $table) {
            $table->id('id');
            $table->string('no_faktur', 50)->nullable();
            $table->string('kode_barang', 50)->nullable();
            $table->string('satuan', 50)->nullable();
            $table->bigInteger('satuan_id')->nullable();
            $table->decimal('qty', 10, 2)->nullable();
            $table->decimal('harga', 15, 2)->nullable();
            $table->decimal('diskon', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->decimal('subtotal', 15, 2)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index(['no_faktur'], 'pembelian_detail_no_faktur');
            $table->index(['kode_barang'], 'pembelian_detail_kode_barang');
            $table->foreign('no_faktur', 'pembelian_detail_ibfk_1')->references('no_faktur')->on('pembelian')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('kode_barang', 'pembelian_detail_ibfk_2')->references('kode_barang')->on('barang')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pembelian_detail');
        Schema::enableForeignKeyConstraints();
    }
};
