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
        Schema::create('pegawai', function (Blueprint $table) {
            $table->string('nip', 20)->primary();
            $table->string('nama_pegawai');
            $table->string('nik', 20)->nullable();
            $table->string('agama')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->date('tgl_masuk')->nullable();
            $table->string('status_pernikahan')->nullable();
            $table->string('no_telp')->nullable();
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();
            $table->string('jabatan');
            $table->string('bagian');
            $table->string('foto')->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->string('institusi')->nullable();
            $table->string('thn_lulus', 4)->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};