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
        Schema::table('penjualan_kiriman', function (Blueprint $table) {
            $table->string('driver_name', 100)->nullable()->after('keterangan');
            $table->string('no_kendaraan', 20)->nullable()->after('driver_name');
            $table->enum('status', ['proses', 'kirim', 'selesai', 'batal'])->default('proses')->after('no_kendaraan');
            $table->string('nama_penerima', 100)->nullable()->after('status');
            $table->string('foto_penerima', 255)->nullable()->after('nama_penerima');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualan_kiriman', function (Blueprint $table) {
            $table->dropColumn(['driver_name', 'no_kendaraan', 'status', 'nama_penerima', 'foto_penerima']);
        });
    }
};
