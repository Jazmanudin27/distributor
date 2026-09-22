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
            $table->string('spv_type', 10)->nullable()->after('is_kanvas')->comment('1 for SPV Sales 1 (all), 2 for SPV Sales 2 (limited)');
        });

        Schema::create('spv_salesman', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('spv_id');
            $table->unsignedBigInteger('sales_id');
            $table->timestamps();

            $table->foreign('spv_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sales_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['spv_id', 'sales_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spv_salesman');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('spv_type');
        });
    }
};
