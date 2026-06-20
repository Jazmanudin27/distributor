<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('penjualan_detail', function (Blueprint $table) {
            $table->id('id');
            $table->string('no_faktur', 50)->nullable();
            $table->string('kode_barang', 50)->nullable();
            $table->bigInteger('satuan_id')->nullable();
            $table->decimal('qty', 10, 2)->nullable();
            $table->decimal('harga', 15, 2)->nullable();
            $table->decimal('subtotal', 15, 2)->nullable();
            $table->decimal('diskon1_persen', 7, 5)->nullable();
            $table->decimal('diskon2_persen', 7, 5)->nullable();
            $table->decimal('diskon3_persen', 7, 5)->nullable();
            $table->decimal('diskon4_persen', 7, 5)->nullable();
            $table->decimal('total_diskon', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->tinyInteger('is_promo')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->decimal('harga_pokok', 15, 0)->nullable();
            $table->index(['no_faktur'], 'penjualan_detail_no_faktur');
            $table->index(['kode_barang'], 'penjualan_detail_kode_barang');
            $table->foreign('no_faktur', 'penjualan_detail_ibfk_1')->references('no_faktur')->on('penjualan')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('kode_barang', 'penjualan_detail_ibfk_2')->references('kode_barang')->on('barang')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('penjualan_detail');
        Schema::enableForeignKeyConstraints();
    }
};
