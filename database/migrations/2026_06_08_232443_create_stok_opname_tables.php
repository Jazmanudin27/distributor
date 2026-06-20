<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('stok_opname', function (Blueprint $table) {
            $table->string('no_opname', 50);
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->char('user_id', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->primary(['no_opname']);
        });

        Schema::create('stok_opname_detail', function (Blueprint $table) {
            $table->id('id');
            $table->string('no_opname', 50)->nullable();
            $table->string('kode_barang', 50)->nullable();
            $table->decimal('stok_sistem', 15, 2)->nullable()->default(0);
            $table->decimal('stok_fisik', 15, 2)->nullable()->default(0);
            $table->decimal('selisih', 15, 2)->nullable()->default(0);
            $table->string('keterangan', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['no_opname'], 'stok_opname_detail_no_opname');
            $table->foreign('no_opname', 'stok_opname_detail_ibfk_1')->references('no_opname')->on('stok_opname')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('kode_barang', 'stok_opname_detail_ibfk_2')->references('kode_barang')->on('barang')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('stok_opname_detail');
        Schema::dropIfExists('stok_opname');
        Schema::enableForeignKeyConstraints();
    }
};
