<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('pengajuan_approvals', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('jenis_pengajuan', ['limit_kredit','double_faktur']);
            $table->string('pengajuan_id', 20);
            $table->string('user_id', 20);
            $table->string('level_approval', 20)->nullable();
            $table->tinyInteger('disetujui')->nullable()->default(0);
            $table->tinyInteger('ditolak')->nullable()->default(0);
            $table->text('keterangan')->nullable();
            $table->dateTime('tanggal_approval')->nullable();
            $table->string('approved_by', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->bigInteger('revisi_limit')->nullable();
            $table->index(['pengajuan_id'], 'pengajuan_approvals_fk_pengajuan_limit');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pengajuan_approvals');
        Schema::enableForeignKeyConstraints();
    }
};
