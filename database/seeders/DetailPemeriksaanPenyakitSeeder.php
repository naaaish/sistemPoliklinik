<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailPemeriksaanPenyakitSeeder extends Seeder
{
    public function run()
    {
        // Pastikan tabel diagnosa, pemeriksaan, dan diagnosa_k3 sudah di-seed
        
        $data_detail = [
            // Kasus 1: Abses Gigi
            [
                'id_pemeriksaan' => 'PMX-001',
                'id_diagnosa'    => 3,     // ABSES GIGI
                'id_nb'          => '2.1', // Inputan Dokter: Masuk Kategori Pencernaan (Contoh Mapping)
            ],
            
            // Kasus 2: Abses Hati (Komplikasi)
            [
                'id_pemeriksaan' => 'PMX-002',
                'id_diagnosa'    => 4,     // ABSES HATI
                'id_nb'          => '2.5', // Inputan Dokter: Radang Hati (Sesuai list K3)
            ],

            // Kasus 3: Abses Kulit (Komplikasi lain di pasien yang sama)
            [
                'id_pemeriksaan' => 'PMX-002', 
                'id_diagnosa'    => 2,     // ABSES BIASA
                'id_nb'          => '1.2', // Inputan Dokter: Infeksi Lain (Contoh)
            ],
        ];

        DB::table('detail_pemeriksaan_penyakit')->insert($data_detail);
    }
}