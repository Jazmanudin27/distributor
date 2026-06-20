<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('pengajuan_limit_kredit', function (Blueprint $table) {
            $table->string('id', 20);
            $table->date('tanggal')->nullable();
            $table->string('kode_pelanggan', 20);
            $table->text('alasan')->nullable();
            $table->bigInteger('nilai_pengajuan')->nullable()->default(0);
            $table->string('nik', 20);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->bigInteger('nilai_disetujui')->nullable();
            $table->char('status', 20)->nullable();
            $table->primary(['id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pengajuan_limit_kredit');
        Schema::enableForeignKeyConstraints();
    }
};
