<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('retur_penjualan_detail', function (Blueprint $table) {
            $table->id('id');
            $table->string('no_retur', 30)->nullable();
            $table->string('kode_barang', 30)->nullable();
            $table->decimal('qty', 12, 2)->nullable()->default(0);
            $table->decimal('harga_retur', 15, 2)->nullable()->default(0);
            $table->decimal('subtotal_retur', 15, 2)->nullable()->default(0);
            $table->decimal('diskon1_persen', 20, 5)->nullable();
            $table->decimal('diskon2_persen', 20, 5)->nullable();
            $table->decimal('diskon3_persen', 20, 5)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->char('id_satuan', 20)->nullable();
            $table->decimal('total_diskon_rupiah', 20, 2)->nullable();
            $table->index(['no_retur', 'kode_barang'], 'retur_penjualan_detail_uq_retur_barang');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('retur_penjualan_detail');
        Schema::enableForeignKeyConstraints();
    }
};
