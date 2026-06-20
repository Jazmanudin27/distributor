<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('hrd_karyawan', function (Blueprint $table) {
            $table->char('nik', 20);
            $table->string('nama_lengkap', 255)->nullable();
            $table->text('alamat')->nullable();
            $table->string('nomor_telepon', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L','P'])->nullable()->default('L');
            $table->date('tgl_masuk')->nullable();
            $table->enum('status_karyawan', ['Tetap','Kontrak'])->nullable();
            $table->string('nomor_ktp', 50)->nullable();
            $table->string('npwp', 50)->nullable();
            $table->string('foto_karyawan', 255)->nullable();
            $table->string('pendidikan_terakhir', 50)->nullable();
            $table->string('nomor_rekening_bank', 50)->nullable();
            $table->string('nama_bank', 50)->nullable();
            $table->enum('status_pernikahan', ['Lajang','Menikah'])->nullable();
            $table->integer('jumlah_anak')->nullable();
            $table->text('catatan')->nullable();
            $table->char('status', 1)->nullable();
            $table->integer('id_kantor')->nullable();
            $table->integer('id_jabatan')->nullable();
            $table->char('id_department', 10)->nullable();
            $table->integer('id_group')->nullable();
            $table->string('password', 255)->nullable();
            $table->string('divisi', 10)->nullable();
            $table->primary(['nik']);
            $table->index(['id_kantor'], 'hrd_karyawan_idx_karyawan_kantor');
            $table->index(['id_jabatan'], 'hrd_karyawan_idx_karyawan_jabatan');
            $table->index(['id_department'], 'hrd_karyawan_idx_karyawan_dept');
            $table->index(['id_group'], 'hrd_karyawan_idx_karyawan_group');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('hrd_karyawan');
        Schema::enableForeignKeyConstraints();
    }
};
