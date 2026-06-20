<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('target_sales', function (Blueprint $table) {
            $table->id('id');
            $table->string('kode_sales', 20);
            $table->integer('tahun');
            $table->integer('bulan');
            $table->bigInteger('target_1')->nullable()->default(0);
            $table->bigInteger('target_2')->nullable()->default(0);
            $table->bigInteger('target_3')->nullable()->default(0);
            $table->bigInteger('target_4')->nullable()->default(0);
            $table->bigInteger('target_5')->nullable()->default(0);
            $table->bigInteger('target_6')->nullable()->default(0);
            $table->bigInteger('target_7')->nullable()->default(0);
            $table->bigInteger('target_8')->nullable()->default(0);
            $table->bigInteger('target_9')->nullable()->default(0);
            $table->bigInteger('target_10')->nullable()->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('target_sales');
        Schema::enableForeignKeyConstraints();
    }
};
