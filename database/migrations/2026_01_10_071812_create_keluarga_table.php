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
        Schema::create('keluarga', function (Blueprint $table) {
            $table->string('id_keluarga', 32)->primary();
            $table->string('nip', 20);

            $table->enum('hubungan_keluarga', ['pasangan','anak']);
            $table->unsignedTinyInteger('urutan_anak')->nullable();
            $table->string('nama_keluarga');
            $table->date('tgl_lahir');
            $table->enum('jenis_kelamin', ['L','P']);
            $table->boolean('is_active')->default(1);

            $table->timestamps();

            $table->foreign('nip')->references('nip')->on('pegawai')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keluarga');
    }
};
