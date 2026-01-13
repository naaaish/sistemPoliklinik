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
        Schema::create('pendaftaran', function (Blueprint $table) {
            $table->string('id_pendaftaran',20)->primary();
            $table->date('tanggal');
            $table->enum('jenis_pemeriksaan',['cek_kesehatan','berobat']);
            $table->text('keluhan')->nullable();
            $table->string('id_pasien',20);
            $table->string('id_dokter',20)->nullable();
            $table->string('id_pemeriksa',20)->nullable();

            $table->foreign('id_pasien')->references('id_pasien')->on('pasien');
            $table->foreign('id_dokter')->references('id_dokter')->on('dokter')->nullOnDelete();
            $table->foreign('id_pemeriksa')->references('id_pemeriksa')->on('pemeriksa')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran');
    }
};
