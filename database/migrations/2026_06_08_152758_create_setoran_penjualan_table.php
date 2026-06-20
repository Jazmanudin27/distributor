<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('setoran_penjualan', function (Blueprint $table) {
            $table->char('kode_setoran', 50);
            $table->date('tanggal')->nullable();
            $table->char('kode_sales', 50)->nullable();
            $table->integer('lhp_tunai')->nullable();
            $table->integer('lhp_tagihan')->nullable();
            $table->integer('setoran_kertas')->nullable();
            $table->integer('setoran_logam')->nullable();
            $table->integer('setoran_lainnya')->nullable();
            $table->integer('setoran_giro')->nullable();
            $table->integer('setoran_transfer')->nullable();
            $table->integer('giro_to_cash')->nullable();
            $table->integer('giro_to_transfer')->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->primary(['kode_setoran']);
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('setoran_penjualan');
        Schema::enableForeignKeyConstraints();
    }
};
