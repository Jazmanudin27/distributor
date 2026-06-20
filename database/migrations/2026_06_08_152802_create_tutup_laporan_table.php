<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('tutup_laporan', function (Blueprint $table) {
            $table->id('id');
            $table->string('bulan', 7);
            $table->string('tahun', 7)->nullable();
            $table->string('kategori', 50);
            $table->tinyInteger('status')->nullable()->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('periode', 7)->nullable();
            $table->unique(['bulan', 'kategori'], 'tutup_laporan_periode');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('tutup_laporan');
        Schema::enableForeignKeyConstraints();
    }
};
