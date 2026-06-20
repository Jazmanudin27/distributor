<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('anggota', function (Blueprint $table) {
            $table->increments('id_anggota');
            $table->string('nik', 20);
            $table->string('nama_anggota', 100);
            $table->text('alamat')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->date('tgl_daftar')->useCurrent();
            $table->enum('status', ['aktif','nonaktif'])->nullable()->default('aktif');
            $table->unique(['nik'], 'anggota_nik');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('anggota');
        Schema::enableForeignKeyConstraints();
    }
};
