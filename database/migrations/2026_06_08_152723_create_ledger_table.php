<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('ledger', function (Blueprint $table) {
            $table->id('id');
            $table->date('tanggal');
            $table->string('kode_bank', 50)->nullable();
            $table->string('pelanggan', 255)->nullable();
            $table->string('nomor_bukti', 50)->nullable();
            $table->string('kode_akun', 20);
            $table->string('keterangan', 255)->nullable();
            $table->char('tipe', 20)->nullable()->default('0.00');
            $table->decimal('jumlah', 18, 2)->nullable()->default(0);
            $table->string('ref_transaksi', 50)->nullable();
            $table->tinyInteger('status')->nullable()->default(1);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->char('id_user', 30)->nullable();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ledger');
        Schema::enableForeignKeyConstraints();
    }
};
