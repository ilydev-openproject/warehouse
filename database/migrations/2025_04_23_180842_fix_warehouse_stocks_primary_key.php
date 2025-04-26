<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('warehouse_stocks', function (Blueprint $table) {
            // 1. Drop foreign keys terlebih dahulu
            $table->dropForeign(['id_product']);
            $table->dropForeign(['id_gudang']);

            // 2. Hapus composite primary key
            $table->dropPrimary(['id_product', 'id_gudang']);

            // 3. Tambah kolom id sebagai primary key baru
            $table->id()->first();

            // 4. Tambah kembali foreign keys
            $table->foreign('id_product')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('id_gudang')->references('id')->on('gudangs')->onDelete('cascade');

            // 5. Tambah unique constraint
            $table->unique(['id_product', 'id_gudang']);
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('warehouse_stocks', function (Blueprint $table) {
            $table->dropUnique(['id_product', 'id_gudang']);
            $table->dropColumn('id');
            $table->primary(['id_product', 'id_gudang']);
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
