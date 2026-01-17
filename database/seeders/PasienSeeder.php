<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PasienSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data_pasien = [
            // --- KELUARGA AHMAD PRATAMA (198765432001) ---
            
            // 1. Ahmad sendiri (YBS)
            [
                'id_pasien'     => 'PS-001',
                'nama_pasien'   => 'Dr. Ahmad Pratama',
                'tipe_pasien'   => 'pegawai',
                'hub_kel'       => 'YBS',
                'tgl_lahir'     => '1990-01-01',
                'nip'           => '198765432001',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            
            // 2. Istri Ahmad
            [
                'id_pasien'     => 'PS-002',
                'nama_pasien'   => 'Dewi Sartika',
                'tipe_pasien'   => 'keluarga',
                'hub_kel'       => 'pasangan',
                'tgl_lahir'     => '1992-05-15',
                'nip'           => '198765432001',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            
            // 3. Anak Ahmad
            [
                'id_pasien'     => 'PS-003',
                'nama_pasien'   => 'Budi Pratama',
                'tipe_pasien'   => 'keluarga',
                'hub_kel'       => 'anak',
                'tgl_lahir'     => '2018-08-17',
                'nip'           => '198765432001',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],

            // --- KELUARGA SITI AISYAH (198765432002) ---

            // 4. Siti sendiri (YBS)
            [
                'id_pasien'     => 'PS-004',
                'nama_pasien'   => 'Siti Aisyah',
                'tipe_pasien'   => 'pegawai',
                'hub_kel'       => 'YBS',
                'tgl_lahir'     => '1992-02-02',
                'nip'           => '198765432002',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            
            // 5. Anak Siti
            [
                'id_pasien'     => 'PS-005',
                'nama_pasien'   => 'Putri Ramadhani',
                'tipe_pasien'   => 'keluarga',
                'hub_kel'       => 'anak',
                'tgl_lahir'     => '2020-12-10',
                'nip'           => '198765432002',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ];

        DB::table('pasien')->insert($data_pasien);
    }
}