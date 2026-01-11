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
        Schema::create('pasien', function (Blueprint $table) {
            $table->string('id_pasien',20)->primary();
            $table->string('nama_pasien');
            $table->enum('hub_kel',['Ybs','Istri/Suami','Anak']);
            $table->date('tgl_lahir');
            $table->string('jenis_kelamin');
            $table->string('nip',20);

            $table->foreign('nip')->references('nip')->on('pegawai');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasien');
    }
};
