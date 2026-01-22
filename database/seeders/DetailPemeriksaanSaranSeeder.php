<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailPemeriksaanSaranSeeder extends Seeder
{
    public function run()
    {
        DB::table('detail_pemeriksaan_saran')->insertOrIgnore([
            // Saran untuk Hipertensi
            ['id_pemeriksaan' => 'PMX-001', 'id_saran' => 'SRN-002'], 
            
            // Saran untuk Flu
            ['id_pemeriksaan' => 'PMX-002', 'id_saran' => 'SRN-001'],
        ]);
    }
}