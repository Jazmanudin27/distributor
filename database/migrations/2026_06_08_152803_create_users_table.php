<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table) {
            $table->id('id');
            $table->string('name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('password', 255)->nullable();
            $table->char('role', 20)->nullable();
            $table->char('nik', 20)->nullable();
            $table->char('status', 1)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->integer('role_id')->nullable();
            $table->string('team', 20)->nullable()->default('1');
            $table->string('divisi', 20)->nullable();
            $table->integer('sales')->nullable();
            $table->char('jenis_sales', 10)->nullable();
            $table->char('jenis_barang', 20)->nullable();
            $table->unique(['email'], 'users_email');
            $table->index(['role'], 'users_idx_users_role_id');
            $table->index(['nik'], 'users_idx_users_nik');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();
    }
};
