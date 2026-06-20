<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('supplier', function (Blueprint $table) {
            $table->string('kode_supplier', 50);
            $table->string('nama_supplier', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_hp', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->tinyInteger('status')->nullable();
            $table->char('ppn', 3)->nullable();
            $table->integer('tempo')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->primary(['kode_supplier']);
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('supplier');
        Schema::enableForeignKeyConstraints();
    }
};
