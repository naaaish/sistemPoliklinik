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
            ],
            [
                'id_dokter' => 'DOK002',
                'nama' => 'dr. Siti Aisyah',
                'jenis_dokter' => 'Dokter Umum',
                'status' => 'Nonaktif',
            ],
        ]);
    }
}