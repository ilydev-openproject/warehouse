<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('warehouse_stocks', function (Blueprint $table) {
            // Drop Foreign Keys
            $table->dropForeign(['id_product']);
            $table->dropForeign(['id_gudang']);

            // Add Unique Constraint
            $table->unique(['id_product', 'id_gudang', 'expired_at']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_stocks', function (Blueprint $table) {
            // Drop Unique Constraint
            $table->dropUnique(['id_product', 'id_gudang', 'expired_at']);

            // Add Foreign Keys back
            $table->foreign('id_product')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('id_gudang')->references('id')->on('warehouses')->onDelete('cascade');
        });
    }
};
