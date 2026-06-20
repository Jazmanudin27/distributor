<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('pelanggan', function (Blueprint $table) {
            $table->string('kode_pelanggan', 50);
            $table->string('nama_pelanggan', 100)->nullable();
            $table->string('alamat_pelanggan', 150)->nullable();
            $table->string('alamat_toko', 150)->nullable();
            $table->date('tanggal_register')->nullable();
            $table->string('no_hp_pelanggan', 30)->nullable();
            $table->enum('kepemilikan', ['Pribadi','Sewa','Lainnya'])->nullable()->default('Pribadi');
            $table->decimal('omset_toko', 15, 2)->nullable();
            $table->decimal('limit_pelanggan', 15, 2)->nullable();
            $table->string('hari', 50)->nullable();
            $table->string('kunjungan', 50)->nullable();
            $table->enum('metode_bayar', ['Cash','Kredit','Transfer'])->nullable()->default('Cash');
            $table->string('latitude', 100)->nullable();
            $table->string('longitude', 100)->nullable();
            $table->tinyInteger('status')->nullable()->default(1);
            $table->string('foto', 255)->nullable();
            $table->integer('kode_wilayah')->nullable();
            $table->string('email', 100)->nullable();
            $table->integer('ljt')->nullable()->default(14);
            $table->integer('max_faktur')->nullable()->default(1);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->useCurrent();
            $table->string('kode_toko', 50)->nullable();
            $table->integer('sub_wilayah')->nullable();
            $table->char('jenis_pelanggan', 30)->nullable();
            $table->integer('approve')->nullable();
            $table->string('foto_ktp', 200)->nullable();
            $table->primary(['kode_pelanggan']);
            $table->index(['kode_wilayah'], 'pelanggan_idx_pelanggan_kode_wilayah');
            $table->foreign('kode_wilayah', 'pelanggan_ibfk_1')->references('kode_wilayah')->on('wilayah');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pelanggan');
        Schema::enableForeignKeyConstraints();
    }
};
