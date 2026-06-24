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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_kanvas')) {
                $table->boolean('is_kanvas')->default(false)->after('status');
            }
        });

        Schema::create('canvas_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('no_canvas')->unique();
            $table->string('kode_sales'); // References users.nik
            $table->date('tanggal');
            $table->string('status', 20)->default('loading'); // loading, completed
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('canvas_session_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('canvas_session_id');
            $table->string('kode_barang');
            $table->unsignedBigInteger('satuan_id')->nullable();
            $table->decimal('qty_ambil', 15, 2)->default(0);
            $table->decimal('qty_terjual', 15, 2)->default(0);
            $table->decimal('qty_kembali', 15, 2)->default(0);
            $table->decimal('selisih', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('canvas_session_id')->references('id')->on('canvas_sessions')->onDelete('cascade');
            $table->foreign('kode_barang')->references('kode_barang')->on('barang')->onDelete('cascade');
            $table->foreign('satuan_id')->references('id')->on('barang_satuan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canvas_session_details');
        Schema::dropIfExists('canvas_sessions');
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_kanvas')) {
                $table->dropColumn('is_kanvas');
            }
        });
    }
};
