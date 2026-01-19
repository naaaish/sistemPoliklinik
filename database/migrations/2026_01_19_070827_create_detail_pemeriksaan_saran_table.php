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
        Schema::create('detail_pemeriksaan_saran', function (Blueprint $table) {
            $table->id();
            $table->string('id_pemeriksaan', 30);
            $table->string('id_saran', 30);

            $table->foreign('id_pemeriksaan')
                ->references('id_pemeriksaan')
                ->on('pemeriksaan')
                ->onDelete('cascade');

            $table->foreign('id_saran')
                ->references('id_saran')
                ->on('saran')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pemeriksaan_saran');
    }
};
