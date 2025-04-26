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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_order')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('id_product')->constrained('products');
            $table->foreignId('id_gudang')->nullable()->constrained('gudangs');

            $table->integer('quantity')->unsigned();
            $table->enum('fulfillment_type', ['warehouse', 'dropship'])->default('warehouse');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
