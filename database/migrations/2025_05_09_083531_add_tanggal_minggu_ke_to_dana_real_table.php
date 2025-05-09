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
        Schema::table('dana_reals', function (Blueprint $table) {
            $table->date('tanggal')->nullable();  // Menambahkan kolom tanggal
            $table->integer('minggu_ke')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dana_real', function (Blueprint $table) {
            $table->dropColumn(['tanggal', 'minggu_ke']);
        });
    }
};
