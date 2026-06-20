<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('penjualan_pembayaran', function (Blueprint $table) {
            $table->id('id');
            $table->string('no_bukti', 50)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('no_faktur', 50)->nullable();
            $table->string('kode_pelanggan', 50)->nullable();
            $table->string('kode_sales', 50)->nullable();
            $table->char('jenis_bayar', 20)->nullable();
            $table->decimal('jumlah', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->bigInteger('id_user')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->decimal('jumlah_setor', 15, 0)->nullable();
            $table->date('tanggal_diterima')->nullable();
            $table->char('status', 20)->nullable()->default('disetujui');
            $table->index(['no_faktur'], 'penjualan_pembayaran_no_faktur');
            $table->index(['kode_pelanggan'], 'penjualan_pembayaran_kode_pelanggan');
            $table->index(['id_user'], 'penjualan_pembayaran_id_user');
            $table->foreign('no_faktur', 'penjualan_pembayaran_ibfk_1')->references('no_faktur')->on('penjualan')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('kode_pelanggan', 'penjualan_pembayaran_ibfk_2')->references('kode_pelanggan')->on('pelanggan')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('penjualan_pembayaran');
        Schema::enableForeignKeyConstraints();
    }
};
