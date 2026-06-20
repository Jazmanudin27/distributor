<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('mutasi_barang_masuk', function (Blueprint $table) {
            $table->string('kode_transaksi', 50);
            $table->char('no_faktur', 50)->nullable();
            $table->char('jenis_pemasukan', 30)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('sumber', 100)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->enum('kondisi', ['gs','bs'])->nullable()->default('gs');
            $table->date('tanggal_diterima')->nullable();
            $table->string('catatan', 255)->nullable();
            $table->primary(['kode_transaksi']);
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('mutasi_barang_masuk');
        Schema::enableForeignKeyConstraints();
    }
};
