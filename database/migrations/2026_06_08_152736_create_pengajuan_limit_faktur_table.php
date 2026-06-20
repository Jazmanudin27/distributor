<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('pengajuan_limit_faktur', function (Blueprint $table) {
            $table->char('id', 20);
            $table->string('kode_pelanggan', 20);
            $table->date('tanggal')->nullable();
            $table->text('alasan')->nullable();
            $table->integer('jumlah_faktur')->nullable();
            $table->enum('status', ['diajukan','disetujui','ditolak'])->nullable()->default('diajukan');
            $table->char('nik', 20);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->primary(['id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pengajuan_limit_faktur');
        Schema::enableForeignKeyConstraints();
    }
};
