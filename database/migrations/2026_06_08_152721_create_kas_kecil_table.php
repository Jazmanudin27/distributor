<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('kas_kecil', function (Blueprint $table) {
            $table->id('id');
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->char('kode_akun', 30)->nullable();
            $table->enum('tipe', ['debet','kredit']);
            $table->decimal('jumlah', 20, 2);
            $table->string('id_user', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('kas_kecil');
        Schema::enableForeignKeyConstraints();
    }
};
