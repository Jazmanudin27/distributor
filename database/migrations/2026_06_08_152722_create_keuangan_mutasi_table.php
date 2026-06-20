<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('keuangan_mutasi', function (Blueprint $table) {
            $table->increments('id');
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->enum('tipe', ['debet', 'kredit']);
            $table->decimal('jumlah', 15, 2);
            $table->bigInteger('kode_bank');
            $table->string('id_user', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('keuangan_mutasi');
        Schema::enableForeignKeyConstraints();
    }
};

