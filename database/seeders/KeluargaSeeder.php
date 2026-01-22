<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class KeluargaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $data_keluarga = [            
            // 1. Istri
            [
                'id_keluarga'       => '198765432001-I-1',
                'nip'               => '198765432001',
                'hubungan_keluarga' => 'pasangan',
                'urutan_anak'       => null,
                'nama_keluarga'     => 'Dewi Sartika',
                'tgl_lahir'         => '1992-05-15',
                'jenis_kelamin'     => 'P',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id_keluarga'       => '198765432001-A-1',
                'nip'               => '198765432001',
                'hubungan_keluarga' => 'anak',
                'urutan_anak'       => 1,
                'nama_keluarga'     => 'Budi Pratama',
                'tgl_lahir'         => '2018-08-17',
                'jenis_kelamin'     => 'L',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // 3. Anak Kedua
            [
                'id_keluarga'       => '198765432001-A-2',
                'nip'               => '198765432001',
                'hubungan_keluarga' => 'anak',
                'urutan_anak'       => 2,
                'nama_keluarga'     => 'Ani Pratama',
                'tgl_lahir'         => '2020-02-20',
                'jenis_kelamin'     => 'P',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],

            // 4. Suami
            [
                'id_keluarga'       => '198765432002-S-1',
                'nip'               => '198765432002',
                'hubungan_keluarga' => 'pasangan',
                'urutan_anak'       => null,
                'nama_keluarga'     => 'Rudi Hartono',
                'tgl_lahir'         => '1989-11-10',
                'jenis_kelamin'     => 'L',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // 5. Anak Pertama
            [
                'id_keluarga'       => '198765432002-A-1',
                'nip'               => '198765432002',
                'hubungan_keluarga' => 'anak',
                'urutan_anak'       => 1,
                'nama_keluarga'     => 'Putri Ramadhani',
                'tgl_lahir'         => '2021-12-10',
                'jenis_kelamin'     => 'P',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            
            // 6. Anak Kedua
            [
                'id_keluarga'       => '198765432002-A-2',
                'nip'               => '198765432002',
                'hubungan_keluarga' => 'anak',
                'urutan_anak'       => 2,
                'nama_keluarga'     => 'Agus Ramadhani',
                'tgl_lahir'         => '2023-03-15',
                'jenis_kelamin'     => 'L',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],

            // 7. Anak Ketiga
            [
                'id_keluarga'       => '198765432002-A-3',
                'nip'               => '198765432002',
                'hubungan_keluarga' => 'anak',
                'urutan_anak'       => 3,
                'nama_keluarga'     => 'Siti Ramadhani',
                'tgl_lahir'         => '2024-05-20',
                'jenis_kelamin'     => 'P',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // 8. Anak Keempat
            [
                'id_keluarga'       => '198765432002-A-4',
                'nip'               => '198765432002',
                'hubungan_keluarga' => 'anak',
                'urutan_anak'       => 4,
                'nama_keluarga'     => 'Dodi Ramadhani',
                'tgl_lahir'         => '2025-07-25',
                'jenis_kelamin'     => 'L',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // istri budi santoso (pensiunan)
            [
                'id_keluarga'       => '198765432003-I-1',
                'nip'               => '198765432003',
                'hubungan_keluarga' => 'pasangan',
                'urutan_anak'       => null,
                'nama_keluarga'     => 'Sari Melati',
                'tgl_lahir'         => '1965-03-22',
                'jenis_kelamin'     => 'P',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            // anak dari budi santoso (pensiunan)
            [
                'id_keluarga'       => '198765432003-A-1',
                'nip'               => '198765432003',
                'hubungan_keluarga' => 'anak',
                'urutan_anak'       => 1,
                'nama_keluarga'     => 'Indah Santoso',
                'tgl_lahir'         => '2015-04-10',
                'jenis_kelamin'     => 'P',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id_keluarga'       => '198765432003-A-2',
                'nip'               => '198765432003',
                'hubungan_keluarga' => 'anak',
                'urutan_anak'       => 2,
                'nama_keluarga'     => 'Rani Santoso',
                'tgl_lahir'         => '2018-09-15',
                'jenis_kelamin'     => 'L',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id_keluarga'       => '198765432003-A-3',
                'nip'               => '198765432003',
                'hubungan_keluarga' => 'anak',
                'urutan_anak'       => 3,
                'nama_keluarga'     => 'Dina Santoso',
                'tgl_lahir'         => '2021-01-20',
                'jenis_kelamin'     => 'P',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id_keluarga'       => '198765432003-A-4',
                'nip'               => '198765432003',
                'hubungan_keluarga' => 'anak',
                'urutan_anak'       => 4,
                'nama_keluarga'     => 'Fajar Santoso',
                'tgl_lahir'         => '2023-06-30',
                'jenis_kelamin'     => 'L',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id_keluarga'       => '198765432004-S-1',
                'nip'               => '198765432004',
                'hubungan_keluarga' => 'pasangan',
                'urutan_anak'       => null,
                'nama_keluarga'     => 'Anton Supriyadi',
                'tgl_lahir'         => '1990-07-25',
                'jenis_kelamin'     => 'L',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id_keluarga'       => '198765432004-A-1',
                'nip'               => '198765432004',
                'hubungan_keluarga' => 'anak',
                'urutan_anak'       => 1,
                'nama_keluarga'     => 'Rina Supriyadi',
                'tgl_lahir'         => '2022-10-05',
                'jenis_kelamin'     => 'P',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id_keluarga'      => '198765432005-S-1',
                'nip'               => '198765432005',
                'hubungan_keluarga' => 'pasangan',
                'urutan_anak'       => null,
                'nama_keluarga'     => 'Alvian Wijaya',
                'tgl_lahir'         => '1992-12-12',
                'jenis_kelamin'     => 'L',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id_keluarga'       => '198765432005-A-1',
                'nip'               => '198765432005',
                'hubungan_keluarga' => 'anak',
                'urutan_anak'       => 1,
                'nama_keluarga'     => 'Nina Wijaya',
                'tgl_lahir'         => '2024-04-18',
                'jenis_kelamin'     => 'P',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id_keluarga'       => '198765432005-A-2',
                'nip'               => '198765432005',
                'hubungan_keluarga' => 'anak',
                'urutan_anak'       => 2,
                'nama_keluarga'     => 'Doni Wijaya',
                'tgl_lahir'         => '2025-08-22',
                'jenis_kelamin'     => 'L',
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
        ];

        DB::table('keluarga')->insert($data_keluarga);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keluarga');
    }
}