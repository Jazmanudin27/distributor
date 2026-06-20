<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('penjualan', function (Blueprint $table) {
            $table->string('no_faktur', 50);
            $table->date('tanggal')->nullable();
            $table->string('kode_pelanggan', 50)->nullable();
            $table->char('kode_sales', 20)->nullable();
            $table->char('jenis_transaksi', 10)->nullable();
            $table->char('jenis_bayar', 10)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->decimal('diskon', 15, 2)->nullable();
            $table->decimal('grand_total', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->char('id_user', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->integer('batal')->nullable()->default(0);
            $table->string('alasan_batal', 255)->nullable();
            $table->date('tanggal_kirim')->nullable();
            $table->bigInteger('cetak')->nullable()->default(0);
            $table->primary(['no_faktur']);
            $table->index(['id_user'], 'penjualan_id_user');
            $table->index(['kode_sales'], 'penjualan_kode_sales');
            $table->index(['kode_pelanggan'], 'penjualan_idx_penjualan_pelanggan');
            $table->foreign('kode_pelanggan', 'penjualan_ibfk_1')->references('kode_pelanggan')->on('pelanggan')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('penjualan');
        Schema::enableForeignKeyConstraints();
    }
};
