<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->foreignId('id_product')->constrained('products')->onDelete('cascade');
            $table->foreignId('id_gudang')->constrained('gudangs')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->timestamps();
            $table->primary(['id_product', 'id_gudang']);
            $table->index(['id_gudang', 'id_product']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_stocks');
    }
};
