<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PasienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Data NIP diambil dari PegawaiSeeder Anda:
        // 198765432001 -> Dr. Ahmad Pratama (Laki-laki)
        // 198765432002 -> Siti Aisyah (Perempuan)

        $data_pasien = [
            // --- KELUARGA AHMAD PRATAMA (198765432001) ---
            
            // 1. Ahmad sendiri (Status: Pegawai, Hub: YBS)
            [
                'id_pasien'     => 'PS-001',
                'nama_pasien'   => 'Dr. Ahmad Pratama', // Sama dengan nama pegawai
                'tipe_pasien'   => 'pegawai',
                'hub_kel'       => 'YBS',
                'tgl_lahir'     => '1990-01-01', // Sama dengan tgl lahir pegawai
                'nip'           => '198765432001',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            // 2. Istri Ahmad (Status: Keluarga, Hub: Pasangan)
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
             // 3. Anak Ahmad (Status: Keluarga, Hub: Anak)
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

            // 4. Siti sendiri (Status: Pegawai, Hub: YBS)
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
            // 5. Anak Siti (Status: Keluarga, Hub: Anak)
            // (Asumsi single parent atau suami tidak terdaftar di sistem ini)
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