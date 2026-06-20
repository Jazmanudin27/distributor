<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->string('no_po', 50);
            $table->date('tanggal');
            $table->date('jatuh_tempo')->nullable();
            $table->string('kode_supplier', 50);
            $table->string('jenis_transaksi', 20)->nullable()->default('kredit');
            $table->bigInteger('potongan')->nullable()->default(0);
            $table->bigInteger('pajak')->nullable()->default(0);
            $table->bigInteger('grand_total')->nullable()->default(0);
            $table->enum('status', ['open','closed','cancel'])->nullable()->default('open');
            $table->text('keterangan')->nullable();
            $table->char('id_user', 20);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->bigInteger('potongan_claim')->nullable();
            $table->primary(['no_po']);
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('purchase_orders');
        Schema::enableForeignKeyConstraints();
    }
};
