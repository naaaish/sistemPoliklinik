<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PemeriksaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pemeriksa')->insert([
            [
                'id_pemeriksa' => 'PMR001',
                'nama_pemeriksa' => 'Perawat Lina',
                'status' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_pemeriksa' => 'PMR002',
                'nama_pemeriksa' => 'Perawat Rudi',
                'status' => 'Aktif',   
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_pemeriksa' => 'PMR003',
                'nama_pemeriksa' => 'Petugas Kesehatan Ani',
                'status' => 'Nonaktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
