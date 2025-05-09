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
        Schema::create('dana_reals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_platform');
            $table->unsignedBigInteger('id_rekening');
            $table->unsignedBigInteger('id_toko');

            $table->decimal('saldo_awal', 15, 2)->nullable();
            $table->decimal('saldo_di_tarik', 15, 2)->nullable()->default(0);
            $table->decimal('iklan', 15, 2)->nullable()->default(0);
            $table->decimal('omset', 15, 2)->nullable()->default(0);
            $table->enum('status', ['success', 'audit']);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_platform', 'fk_dana_reals_id_platform')->references('id')->on('platforms')->onDelete('cascade');
            $table->foreign('id_rekening', 'fk_dana_reals_id_rekening')->references('id')->on('rekenings')->onDelete('cascade');
            $table->foreign('id_toko', 'fk_dana_reals_id_toko')->references('id')->on('tokos')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dana_reals');
    }
};
