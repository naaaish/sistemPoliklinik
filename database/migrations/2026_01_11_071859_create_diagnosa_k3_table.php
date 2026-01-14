<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnosa_k3', function (Blueprint $table) {
            $table->string('id_nb', 10)->primary(); 
            $table->text('nama_penyakit');
            $table->string('kategori_penyakit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosa_k3');
    }
};