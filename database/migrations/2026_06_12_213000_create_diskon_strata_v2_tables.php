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
        Schema::disableForeignKeyConstraints();
        
        // Drop old single flat table if it exists
        Schema::dropIfExists('diskon_strata');
        
        // 1. Create diskon_strata (Header)
        Schema::create('diskon_strata', function (Blueprint $table) {
            $table->id();
            $table->string('nama_diskon', 150);
            $table->enum('tipe', ['barang', 'beberapa_barang', 'kategori', 'merk', 'supplier']);
            
            // Targets (polymorphic-like references)
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->unsignedBigInteger('merk_id')->nullable();
            $table->string('kode_supplier', 50)->nullable();
            
            $table->dateTime('berlaku_dari')->nullable();
            $table->dateTime('berlaku_sampai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('kategori_id')->references('id')->on('kategori')->onDelete('cascade');
            $table->foreign('merk_id')->references('id')->on('merk')->onDelete('cascade');
            $table->foreign('kode_supplier')->references('kode_supplier')->on('supplier')->onDelete('cascade');
        });

        // 2. Create diskon_strata_barang (Pivot for item mapping)
        Schema::create('diskon_strata_barang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('diskon_strata_id');
            $table->string('kode_barang', 50);
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('diskon_strata_id')->references('id')->on('diskon_strata')->onDelete('cascade');
            $table->foreign('kode_barang')->references('kode_barang')->on('barang')->onDelete('cascade');
        });

        // 3. Create diskon_strata_detail (Strata/Tiers)
        Schema::create('diskon_strata_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('diskon_strata_id');
            
            // Tiers (can use quantity thresholds or purchase nominal thresholds)
            $table->integer('min_qty')->nullable();
            $table->integer('max_qty')->nullable();
            $table->decimal('min_nominal', 15, 2)->nullable();
            $table->decimal('max_nominal', 15, 2)->nullable();
            
            // Discount values
            $table->enum('tipe_nilai', ['persen', 'nominal'])->default('persen');
            $table->decimal('dis1', 15, 2)->default(0); // regular/kredit discount
            $table->decimal('dis2', 15, 2)->default(0); // cash discount
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('diskon_strata_id')->references('id')->on('diskon_strata')->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        
        Schema::dropIfExists('diskon_strata_detail');
        Schema::dropIfExists('diskon_strata_barang');
        Schema::dropIfExists('diskon_strata');
        
        Schema::enableForeignKeyConstraints();
    }
};
