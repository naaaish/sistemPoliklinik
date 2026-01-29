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
        Schema::create('pemeriksaan', function (Blueprint $table) {
            $table->string('id_pemeriksaan',20)->primary();
            $table->integer('sistol')->nullable();
            $table->integer('diastol')->nullable();
            $table->integer('nadi')->nullable();
            $table->integer('gd_puasa')->nullable();
            $table->integer('gd_duajam')->nullable();
            $table->integer('gd_sewaktu')->nullable();
            $table->float('asam_urat')->nullable();
            $table->integer('chol')->nullable();
            $table->integer('tg')->nullable();
            $table->float('suhu')->nullable();
            $table->float('berat')->nullable();
            $table->float('tinggi')->nullable();
            $table->string('id_saran',20)->nullable();

            $table->string('id_pendaftaran',20);

            $table->foreign('id_saran')->references('id_saran')->on('saran');
            $table->foreign('id_pendaftaran')->references('id_pendaftaran')->on('pendaftaran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan');
    }
};
