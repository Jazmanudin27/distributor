<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('purchase_order_detail', function (Blueprint $table) {
            $table->string('no_po', 50)->nullable();
            $table->string('satuan_id', 50);
            $table->integer('qty')->unsigned();
            $table->decimal('harga', 15, 2);
            $table->decimal('diskon', 15, 2)->nullable()->default(0);
            $table->decimal('subtotal', 20, 2)->nullable();
            $table->index(['no_po'], 'purchase_order_detail_no_po');
            $table->foreign('no_po', 'purchase_order_detail_ibfk_1')->references('no_po')->on('purchase_orders')->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('purchase_order_detail');
        Schema::enableForeignKeyConstraints();
    }
};
