<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->integer('ljt')->nullable()->default(30)->change();
        });

        // Update existing ljt = 14 or NULL to 30
        DB::table('pelanggan')
            ->where('ljt', 14)
            ->orWhereNull('ljt')
            ->update(['ljt' => 30]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->integer('ljt')->nullable()->default(14)->change();
        });

        DB::table('pelanggan')
            ->where('ljt', 30)
            ->update(['ljt' => 14]);
    }
};
