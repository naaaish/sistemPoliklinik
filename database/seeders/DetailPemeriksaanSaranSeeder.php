<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailPemeriksaanSaranSeeder extends Seeder
{
    public function run()
    {
        DB::table('detail_pemeriksaan_saran')->insert([
            [
                'id_pemeriksaan' => 'PMX-001',
                'id_saran' => 'SRN-001',
            ],
            [
                'id_pemeriksaan' => 'PMX-001',
                'id_saran' => 'SRN-002',
            ],
        ]);
    }
}
