<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailPemeriksaanPenyakitSeeder extends Seeder
{
    public function run()
    {
        DB::table('detail_pemeriksaan_penyakit')->insertOrIgnore([
            // Kasus 1: Kena Hipertensi (DG-002) & Sakit Kepala (DG-012)
            ['id_pemeriksaan' => 'PMX-001', 'id_diagnosa' => 'DG-002'], 
            ['id_pemeriksaan' => 'PMX-001', 'id_diagnosa' => 'DG-012'],

            // Kasus 2: Kena Flu (DG-001)
            ['id_pemeriksaan' => 'PMX-002', 'id_diagnosa' => 'DG-001'],
        ]);
    }
}