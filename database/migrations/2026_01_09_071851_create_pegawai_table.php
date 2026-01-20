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
            $table->string('nip',20)->primary();
            $table->string('nama_pegawai');
            $table->string('nik',20);
            $table->string('agama');
            $table->string('jenis_kelamin');
            $table->date('tgl_lahir');
            $table->date('tgl_masuk');
            $table->string('status');
            $table->string('status_pernikahan');
            $table->string('no_telp');
            $table->string('email');
            $table->text('alamat');
            $table->string('jabatan');
            $table->string('bagian');
            $table->string('foto')->nullable();
            $table->string('pendidikan_terakhir');
            $table->string('institusi');
            $table->string('thn_lulus',4);
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
