<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailPemeriksaanPenyakitSeeder extends Seeder
{
    public function run()
    {
        DB::table('detail_pemeriksaan_penyakit')->insert([
            [
                'id_pemeriksaan' => 'PMX-001',
                'id_diagnosa' => 'DG-001',
            ],
            [
                'id_pemeriksaan' => 'PMX-001',
                'id_diagnosa' => 'DG-002',
            ],
        ]);
    }
}
