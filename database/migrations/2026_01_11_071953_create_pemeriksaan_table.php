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
            $table->integer('sistol');
            $table->integer('diastol');
            $table->integer('nadi');
            $table->integer('gd_puasa');
            $table->integer('gd_duajam');
            $table->integer('gd_sewaktu');
            $table->float('asam_urat');
            $table->integer('chol');
            $table->integer('tg');
            $table->float('suhu');
            $table->float('berat');
            $table->float('tinggi');

            $table->string('id_pendaftaran',20);
            $table->string('id_diagnosa',20);
            $table->string('id_saran',20);
            $table->string('id_nb',20);

            $table->foreign('id_pendaftaran')->references('id_pendaftaran')->on('pendaftaran');
            $table->foreign('id_diagnosa')->references('id_diagnosa')->on('diagnosa');
            $table->foreign('id_saran')->references('id_saran')->on('saran');
            $table->foreign('id_nb')->references('id_nb')->on('diagnosa_k3');
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
