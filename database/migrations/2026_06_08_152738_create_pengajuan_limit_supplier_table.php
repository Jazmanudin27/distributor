<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('pengajuan_limit_supplier', function (Blueprint $table) {
            $table->id('id');
            $table->string('pengajuan_id', 20);
            $table->string('kode_supplier', 20);
            $table->bigInteger('limit_per_supplier')->default(0);
            $table->bigInteger('sisa_limit')->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->index(['pengajuan_id'], 'pengajuan_limit_supplier_fk_pengajuan_kredit');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pengajuan_limit_supplier');
        Schema::enableForeignKeyConstraints();
    }
};
