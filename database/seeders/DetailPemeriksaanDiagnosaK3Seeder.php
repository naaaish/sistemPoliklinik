<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailPemeriksaanDiagnosaK3Seeder extends Seeder
{
    public function run()
    {
        // 4.1 adalah kode K3 untuk Hypertensi (sesuai DiagnosaK3Seeder)
        // 1.1 adalah kode K3 untuk ISPA/Flu
        DB::table('detail_pemeriksaan_diagnosa_k3')->insertOrIgnore([
            ['id_pemeriksaan' => 'PMX-001', 'id_nb' => '4.1'], 
            ['id_pemeriksaan' => 'PMX-002', 'id_nb' => '1.1'],
        ]);
    }
}