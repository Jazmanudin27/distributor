<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('pembelian', function (Blueprint $table) {
            $table->string('no_faktur', 50);
            $table->string('no_po', 20)->nullable();
            $table->date('tanggal')->nullable();
            $table->date('jatuh_tempo')->nullable();
            $table->string('kode_supplier', 50)->nullable();
            $table->enum('jenis_transaksi', ['Kredit'])->nullable()->default('Kredit');
            $table->decimal('potongan', 15, 2)->nullable();
            $table->decimal('pajak', 15, 2)->nullable();
            $table->decimal('biaya_lain', 15, 2)->nullable();
            $table->decimal('potongan_claim', 15, 2)->nullable();
            $table->decimal('grand_total', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->char('id_user', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->date('tanggal_approve')->nullable();
            $table->primary(['no_faktur']);
            $table->index(['id_user'], 'pembelian_id_user');
            $table->index(['kode_supplier'], 'pembelian_idx_pembelian_supplier');
            $table->foreign('kode_supplier', 'pembelian_ibfk_1')->references('kode_supplier')->on('supplier')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pembelian');
        Schema::enableForeignKeyConstraints();
    }
};
