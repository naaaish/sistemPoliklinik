<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PemeriksaanSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            // HASIL PEMERIKSAAN KASUS 1 (Pegawai - Hipertensi)
            [
                'id_pemeriksaan' => 'PMX-001',
                'id_pendaftaran' => 'DFT-001',
                'sistol'         => 160, // Tinggi
                'diastol'        => 100, // Tinggi
                'nadi'           => 88,
                'gd_puasa'       => 110,
                'gd_duajam'      => 140,
                'gd_sewaktu'     => 130,
                'asam_urat'      => 6.5,
                'chol'           => 210,
                'tg'             => 150,
                'suhu'           => 36.5,
                'berat'          => 75,
                'tinggi'         => 170,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            // HASIL PEMERIKSAAN KASUS 2 (Anak - Demam)
            [
                'id_pemeriksaan' => 'PMX-002',
                'id_pendaftaran' => 'DFT-002',
                'sistol'         => 110,
                'diastol'        => 70,
                'nadi'           => 100, // Agak cepat karena demam
                'gd_puasa'       => null,
                'gd_duajam'      => null,
                'gd_sewaktu'     => null,
                'asam_urat'      => null,
                'chol'           => null,
                'tg'             => null,
                'suhu'           => 38.5, // Demam
                'berat'          => 25,
                'tinggi'         => 120,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]
        ];

        DB::table('pemeriksaan')->insertOrIgnore($data);
    }
}