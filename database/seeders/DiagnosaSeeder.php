<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DiagnosaSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            [
                'id_diagnosa'      => 1,
                'diagnosa'         => 'ABETALIPOPROTEINEMIA',
                'keterangan'       => 'Gangguan penyerapan lemak karena kekurangan apolipoprotein B',
                'klasifikasi_nama' => 'GENETIC',
                'bagian_tubuh'     => 'DARAH',
                'is_active'        => true,
            ],
            [
                'id_diagnosa'      => 2,
                'diagnosa'         => 'ABSES',
                'keterangan'       => 'Kumpulan nanah yang disebabkan oleh infeksi bakteri',
                'klasifikasi_nama' => 'INFECTION',
                'bagian_tubuh'     => 'KULIT',
                'is_active'        => true,
            ],
            [
                'id_diagnosa'      => 3,
                'diagnosa'         => 'ABSES GIGI',
                'keterangan'       => 'Infeksi pada akar gigi atau gusi yang menyebabkan penumpukan nanah',
                'klasifikasi_nama' => 'DENTAL',
                'bagian_tubuh'     => 'GIGI',
                'is_active'        => true,
            ],
            [
                'id_diagnosa'      => 4,
                'diagnosa'         => 'ABSES HATI',
                'keterangan'       => 'Kumpulan nanah di dalam hati akibat infeksi',
                'klasifikasi_nama' => 'HEPATOBILIARY',
                'bagian_tubuh'     => 'HATI',
                'is_active'        => true,
            ],
            [
                'id_diagnosa'      => 5,
                'diagnosa'         => 'ABSES OTAK',
                'keterangan'       => 'Penumpukan nanah dalam jaringan otak',
                'klasifikasi_nama' => 'INFECTION',
                'bagian_tubuh'     => 'OTAK',
                'is_active'        => true,
            ],
        ];

        foreach ($data as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        // Upsert agar tidak error jika dijalankan berulang
        DB::table('diagnosa')->upsert(
            $data, 
            ['id_diagnosa'], 
            ['diagnosa', 'keterangan', 'klasifikasi_nama', 'bagian_tubuh', 'updated_at']
        );
    }
}