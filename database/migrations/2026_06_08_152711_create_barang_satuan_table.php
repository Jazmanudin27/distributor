<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('barang_satuan', function (Blueprint $table) {
            $table->id('id');
            $table->string('kode_barang', 50)->nullable();
            $table->string('satuan', 50)->nullable();
            $table->integer('isi')->nullable();
            $table->decimal('harga_pokok', 15, 2)->nullable();
            $table->decimal('harga_jual', 15, 2)->nullable();
            $table->decimal('harga_khusus', 15, 2)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index(['kode_barang'], 'barang_satuan_idx_barang_satuan_barang');
            $table->foreign('kode_barang', 'barang_satuan_ibfk_1')->references('kode_barang')->on('barang');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('barang_satuan');
        Schema::enableForeignKeyConstraints();
    }
};
