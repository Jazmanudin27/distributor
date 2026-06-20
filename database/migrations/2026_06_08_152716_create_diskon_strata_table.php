<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('diskon_strata', function (Blueprint $table) {
            $table->id('id');
            $table->string('kode_barang', 50)->nullable();
            $table->integer('satuan_id')->nullable();
            $table->decimal('persentase', 7, 5)->nullable();
            $table->integer('syarat')->nullable();
            $table->enum('tipe_syarat', ['qty','nominal'])->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->char('jenis_diskon', 50)->nullable();
            $table->integer('cash')->nullable()->default(0);
            $table->string('kode_supplier', 50)->nullable();
            $table->index(['kode_barang'], 'diskon_strata_idx_diskon_strata_barang');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('diskon_strata');
        Schema::enableForeignKeyConstraints();
    }
};
