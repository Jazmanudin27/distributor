<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('penjualan_kiriman', function (Blueprint $table) {
            $table->id('id');
            $table->date('tanggal');
            $table->integer('kode_wilayah')->nullable();
            $table->string('no_faktur', 50);
            $table->text('keterangan')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->integer('kirimanke')->nullable()->default(1);
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('penjualan_kiriman');
        Schema::enableForeignKeyConstraints();
    }
};
