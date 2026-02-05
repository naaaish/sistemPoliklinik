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
                'jenis_dokter' => 'Dokter Poliklinik',
                'no_telepon' => '6281328716960',
                'status' => 'Aktif',
            ],
            [
                'id_dokter' => 'DOK002',
                'nama' => 'dr Hening Widiawati',
                'jenis_dokter' => 'Dokter Perusahaan',
                'no_telepon' => '6281216355396',
                'status' => 'Aktif',
            ],
        ]);
    }
}