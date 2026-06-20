<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('retur_pembelian', function (Blueprint $table) {
            $table->string('no_retur', 30);
            $table->date('tanggal');
            $table->string('jenis_retur', 30)->nullable();
            $table->string('kode_supplier', 30)->nullable();
            $table->string('no_faktur', 30)->nullable();
            $table->decimal('total', 15, 2)->nullable()->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->char('user_id', 30)->nullable();
            $table->char('kondisi', 30)->nullable();
            $table->primary(['no_retur']);
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('retur_pembelian');
        Schema::enableForeignKeyConstraints();
    }
};
