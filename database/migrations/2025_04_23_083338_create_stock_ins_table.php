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
        Schema::create('stock_ins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_product');
            $table->foreign('id_product')->references('id')->on('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('harga', 18, 2);
            $table->decimal('total_harga', 18, 2);
            $table->unsignedBigInteger('id_gudang');
            $table->foreign('id_gudang')->references('id')->on('gudangs')->onDelete('cascade');
            $table->string('keterangan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ins');
    }
};
