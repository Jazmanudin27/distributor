<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('barang', function (Blueprint $table) {
            $table->string('kode_barang', 50);
            $table->string('nama_barang', 255)->nullable();
            $table->string('kategori', 100)->nullable();
            $table->string('merk', 100)->nullable();
            $table->text('keterangan')->nullable();
            $table->integer('stok_min')->nullable();
            $table->string('kode_supplier', 50)->nullable();
            $table->tinyInteger('status')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->char('jenis', 10)->nullable();
            $table->string('kode_item', 50)->nullable();
            $table->primary(['kode_barang']);
            $table->index(['kode_supplier'], 'barang_idx_barang_supplier');
            $table->foreign('kode_supplier', 'barang_ibfk_1')->references('kode_supplier')->on('supplier');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('barang');
        Schema::enableForeignKeyConstraints();
    }
};
