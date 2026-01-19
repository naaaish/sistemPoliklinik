<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('detail_pemeriksaan_diagnosa_k3', function (Blueprint $table) {
            $table->id();
            $table->string('id_pemeriksaan', 30);
            $table->string('id_nb', 30);

            $table->foreign('id_pemeriksaan')
                ->references('id_pemeriksaan')
                ->on('pemeriksaan')
                ->onDelete('cascade');

            $table->foreign('id_nb')
                ->references('id_nb')
                ->on('diagnosa_k3')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pemeriksaan_diagnosa_k3');
    }
};
