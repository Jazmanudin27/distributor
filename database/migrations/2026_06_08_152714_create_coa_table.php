<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('coa', function (Blueprint $table) {
            $table->increments('id_coa');
            $table->string('kode_akun', 20);
            $table->string('nama_akun', 100);
            $table->string('sub_akun', 20)->nullable();
            $table->tinyInteger('level')->default(1);
            $table->tinyInteger('status')->nullable()->default(1);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->unique(['kode_akun'], 'coa_kode_coa');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('coa');
        Schema::enableForeignKeyConstraints();
    }
};
