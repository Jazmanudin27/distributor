<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('penjualan_pembayaran_transfer', function (Blueprint $table) {
            $table->char('kode_transfer', 50);
            $table->string('no_faktur', 50)->nullable();
            $table->string('kode_pelanggan', 50)->nullable();
            $table->string('kode_sales', 50)->nullable();
            $table->char('jenis_bayar', 20)->nullable();
            $table->decimal('jumlah', 15, 2)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('bank_pengirim', 100)->nullable();
            $table->char('status', 20)->nullable()->default('disetujui');
            $table->text('keterangan')->nullable();
            $table->char('id_user', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->date('tanggal_diterima')->nullable();
            $table->primary(['kode_transfer']);
            $table->index(['no_faktur'], 'penjualan_pembayaran_transfer_no_faktur');
            $table->index(['kode_pelanggan'], 'penjualan_pembayaran_transfer_kode_pelanggan');
            $table->index(['id_user'], 'penjualan_pembayaran_transfer_id_user');
            $table->foreign('no_faktur', 'penjualan_pembayaran_transfer_ibfk_1')->references('no_faktur')->on('penjualan')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('penjualan_pembayaran_transfer');
        Schema::enableForeignKeyConstraints();
    }
};
