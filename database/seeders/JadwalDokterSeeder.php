<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalDokterSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('jadwal_dokter')->insert([
            [
                'hari' => 'Senin',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'id_dokter' => 'DOK001',
            ],
            [
                'hari' => 'Selasa',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'id_dokter' => 'DOK002',
            ],
            [
                'hari' => 'Rabu',
                'jam_mulai' => '13:00:00',
                'jam_selesai' => '16:00:00',
                'id_dokter' => 'DOK001',
            ],
            [
                'hari' => 'Kamis',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '12:00:00',
                'id_dokter' => 'DOK001',
            ],
            [
                'hari' => 'Jumat',
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '11:00:00',
                'id_dokter' => 'DOK002',
            ],
        ]);
    }
}
