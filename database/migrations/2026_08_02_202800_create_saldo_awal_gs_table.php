<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('saldo_awal_gs')) {
            Schema::create('saldo_awal_gs', function (Blueprint $table) {
                $table->id();
                $table->string('kode_barang', 50)->index();
                $table->integer('bulan')->nullable();
                $table->integer('tahun')->nullable();
                $table->date('tanggal')->nullable();
                $table->decimal('qty', 15, 2)->default(0);
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->unique(['kode_barang', 'bulan', 'tahun'], 'saldo_awal_gs_uniq_saldo_barang_bulantahun');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldo_awal_gs');
    }
};
