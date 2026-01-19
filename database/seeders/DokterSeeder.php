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
                'nama' => 'dr Farah Heniyati',
                'jenis_dokter' => 'Dokter Umum',
                'status' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_dokter' => 'DOK002',
                'nama' => 'dr Hening Widiawati',
                'jenis_dokter' => 'Dokter Perusahaan',
                'status' => 'Nonaktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}