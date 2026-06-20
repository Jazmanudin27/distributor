<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('retur_pembelian_detail', function (Blueprint $table) {
            $table->id('id');
            $table->string('no_retur', 30)->nullable();
            $table->string('kode_barang', 30)->nullable();
            $table->bigInteger('satuan_id')->nullable();
            $table->decimal('qty', 12, 2)->nullable()->default(0);
            $table->decimal('harga_retur', 15, 2)->nullable()->default(0);
            $table->decimal('subtotal_retur', 15, 2)->nullable()->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->decimal('diskon1_persen', 20, 5)->nullable();
            $table->decimal('diskon2_persen', 20, 5)->nullable();
            $table->decimal('diskon3_persen', 20, 5)->nullable();
            $table->decimal('diskon4_persen', 20, 5)->nullable();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('retur_pembelian_detail');
        Schema::enableForeignKeyConstraints();
    }
};
