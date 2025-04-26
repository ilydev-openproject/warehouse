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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('resi')->unique();
            // Relasi
            $table->foreignId('id_platform')->constrained('platforms');
            $table->string('customer_name');
            $table->string('alamat');

            // Data transaksi
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->nullable();

            // Status & metadata
            $table->enum('status', ['process', 'shipped', 'returned', 'lost']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
