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
        Schema::create('resep', function (Blueprint $table) {
            $table->string('id_resep',20)->primary();
            $table->decimal('total_tagihan',12,2);
            $table->string('id_pemeriksaan',20);

            $table->foreign('id_pemeriksaan')->references('id_pemeriksaan')->on('pemeriksaan');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resep');
    }
};
