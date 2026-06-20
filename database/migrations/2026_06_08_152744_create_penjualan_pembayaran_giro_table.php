<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('penjualan_pembayaran_giro', function (Blueprint $table) {
            $table->char('kode_giro', 20);
            $table->string('no_faktur', 50)->nullable();
            $table->string('kode_pelanggan', 50)->nullable();
            $table->string('kode_sales', 50)->nullable();
            $table->decimal('jumlah', 15, 2)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('bank_pengirim', 100)->nullable();
            $table->char('status', 30)->nullable()->default('disetujui');
            $table->text('keterangan')->nullable();
            $table->date('jatuh_tempo')->nullable();
            $table->char('no_giro', 50)->nullable();
            $table->char('jenis_bayar', 30)->nullable();
            $table->bigInteger('id_user')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->date('tanggal_cair')->nullable();
            $table->primary(['kode_giro']);
            $table->index(['no_faktur'], 'penjualan_pembayaran_giro_no_faktur');
            $table->index(['kode_pelanggan'], 'penjualan_pembayaran_giro_kode_pelanggan');
            $table->index(['id_user'], 'penjualan_pembayaran_giro_id_user');
            $table->foreign('no_faktur', 'penjualan_pembayaran_giro_ibfk_1')->references('no_faktur')->on('penjualan')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('kode_pelanggan', 'penjualan_pembayaran_giro_ibfk_2')->references('kode_pelanggan')->on('pelanggan')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('penjualan_pembayaran_giro');
        Schema::enableForeignKeyConstraints();
    }
};
