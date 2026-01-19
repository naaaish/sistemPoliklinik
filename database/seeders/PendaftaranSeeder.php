<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PendaftaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil data ID dari tabel relasi agar tidak error Foreign Key
        // Menggunakan 'inRandomOrder()->first()' untuk mengambil 1 data acak
        $pasien = DB::table('pasien')->pluck('id_pasien')->toArray();
        $dokter = DB::table('dokter')->pluck('id_dokter')->toArray();
        $pemeriksa = DB::table('pemeriksa')->pluck('id_pemeriksa')->toArray();

        // Cek apakah data relasi ada. Jika kosong, hentikan seeder.
        if (empty($pasien)) {
            $this->command->warn("Data Pasien kosong! Harap seed tabel pasien terlebih dahulu.");
            return;
        }

        $now = Carbon::now();
        $today = $now->format('Y-m-d');

        $data_pendaftaran = [
            // KASUS 1: Pasien Berobat (Ada Keluhan, Ditangani Dokter)
            [
                'id_pendaftaran'   => 'REG-' . $now->format('ymd') . '01',
                'tanggal'          => $today,
                'jenis_pemeriksaan'=> 'berobat',
                'keluhan'          => 'Demam tinggi sudah 3 hari disertai pusing.',
                // Ambil pasien acak atau fallback ke string dummy jika array kosong
                'id_pasien'        => $pasien[array_rand($pasien)], 
                'id_dokter'        => !empty($dokter) ? $dokter[array_rand($dokter)] : null,
                'id_pemeriksa'     => null, // Biasanya berobat langsung ke dokter
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            // KASUS 2: Pasien Cek Kesehatan (MCU/KIR, Ditangani Pemeriksa/Perawat)
            [
                'id_pendaftaran'   => 'REG-' . $now->format('ymd') . '02',
                'tanggal'          => $today,
                'jenis_pemeriksaan'=> 'cek_kesehatan',
                'keluhan'          => null, // Cek kesehatan biasanya tanpa keluhan sakit
                'id_pasien'        => $pasien[array_rand($pasien)],
                'id_dokter'        => null,
                'id_pemeriksa'     => !empty($pemeriksa) ? $pemeriksa[array_rand($pemeriksa)] : null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            // KASUS 3: Pasien Berobat (Sakit Perut)
            [
                'id_pendaftaran'   => 'REG-' . $now->format('ymd') . '03',
                'tanggal'          => $today,
                'jenis_pemeriksaan'=> 'berobat',
                'keluhan'          => 'Mual dan nyeri ulu hati (kemungkinan maag).',
                'id_pasien'        => $pasien[array_rand($pasien)],
                'id_dokter'        => !empty($dokter) ? $dokter[array_rand($dokter)] : null,
                'id_pemeriksa'     => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
             // KASUS 4: Pasien Berobat (Kecelakaan Kerja Ringan)
            [
                'id_pendaftaran'   => 'REG-' . $now->format('ymd') . '04',
                'tanggal'          => $today,
                'jenis_pemeriksaan'=> 'berobat',
                'keluhan'          => 'Luka sayat pada tangan kanan saat bekerja.',
                'id_pasien'        => $pasien[array_rand($pasien)],
                'id_dokter'        => !empty($dokter) ? $dokter[array_rand($dokter)] : null,
                'id_pemeriksa'     => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        DB::table('pendaftaran')->insert($data_pendaftaran);
    }
}