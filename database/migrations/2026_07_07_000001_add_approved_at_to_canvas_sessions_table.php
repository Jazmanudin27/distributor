<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tambah kolom approved_at untuk menyimpan waktu DPB di-approve (status berubah ke 'loading').
     * Kolom ini digunakan sebagai patokan awal query faktur penjualan di halaman setoran,
     * agar data Section I (qty_terjual) dan Section II (rincian faktur) sinkron.
     */
    public function up(): void
    {
        Schema::table('canvas_sessions', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('canvas_sessions', function (Blueprint $table) {
            $table->dropColumn('approved_at');
        });
    }
};
