<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailPemeriksaanDiagnosaK3Seeder extends Seeder
{
    public function run()
    {
        DB::table('detail_pemeriksaan_diagnosa_k3')->insert([
            [
                'id_pemeriksaan' => 'PMX-001',
                'id_nb' => '2.7',
            ],
        ]);
    }
}
