<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('pembelian_pembayaran', function (Blueprint $table) {
            $table->id('id');
            $table->string('no_bukti', 50)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('no_faktur', 50)->nullable();
            $table->string('jenis_bayar', 50)->nullable();
            $table->decimal('jumlah', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index(['no_faktur'], 'pembelian_pembayaran_no_faktur');
            $table->foreign('no_faktur', 'pembelian_pembayaran_ibfk_1')->references('no_faktur')->on('pembelian')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pembelian_pembayaran');
        Schema::enableForeignKeyConstraints();
    }
};
