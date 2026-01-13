<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DokterSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('dokter')->insert([
            [
                'id_dokter' => 'DOK001',
                'nama' => 'dr. Andi Pratama',
                'jenis_dokter' => 'Dokter Umum',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_dokter' => 'DOK002',
                'nama' => 'dr. Siti Aisyah',
                'jenis_dokter' => 'Dokter Umum',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_dokter' => 'DOK003',
                'nama' => 'dr. Budi Santoso',
                'jenis_dokter' => 'Dokter Perusahaan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}