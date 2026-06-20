<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('mutasi_barang_keluar', function (Blueprint $table) {
            $table->string('kode_transaksi', 50)->default('');
            $table->date('tanggal')->nullable();
            $table->char('jenis_pengeluaran', 30)->nullable();
            $table->string('tujuan', 100)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->char('no_faktur', 50)->nullable();
            $table->char('kode_pelanggan', 50)->nullable();
            $table->enum('kondisi', ['gs','bs'])->nullable()->default('gs');
            $table->date('tanggal_dikirim')->nullable();
            $table->string('catatan', 255)->nullable();
            $table->primary(['kode_transaksi']);
            $table->index(['kode_pelanggan'], 'mutasi_barang_keluar_idx_keluar_kode_pelanggan');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('mutasi_barang_keluar');
        Schema::enableForeignKeyConstraints();
    }
};
