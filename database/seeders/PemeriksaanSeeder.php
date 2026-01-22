<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PemeriksaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil semua ID dari tabel referensi
        $list_pendaftaran = DB::table('pendaftaran')->pluck('id_pendaftaran')->toArray();
        $list_diagnosa    = DB::table('diagnosa')->pluck('id_diagnosa')->toArray();
        $list_saran       = DB::table('saran')->pluck('id_saran')->toArray();
        $list_k3          = DB::table('diagnosa_k3')->pluck('id_nb')->toArray();

        // Cek jika data pendaftaran kosong
        if (empty($list_pendaftaran)) {
            $this->command->warn("Tabel 'pendaftaran' kosong. Harap seed pendaftaran dulu.");
            return;
        }

        $now = Carbon::now();
        $data_pemeriksaan = [];

        // 2. Loop setiap pendaftaran untuk dibuatkan hasil pemeriksaannya
        foreach ($list_pendaftaran as $index => $id_daftar) {
            
            // Generate ID unik, misal: PMX-001, PMX-002
            // Menggunakan index loop agar urut
            $nomor_urut = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $id_pemeriksaan = 'PMX-' . $nomor_urut;

            // Logika acak sederhana:
            // Jika index genap = Sakit (ada diagnosa)
            // Jika index ganjil = Sehat/MCU (diagnosa null)
            $is_sakit = ($index % 2 == 0);

            $data_pemeriksaan[] = [
                'id_pemeriksaan' => $id_pemeriksaan,
                'id_pendaftaran' => $id_daftar, // Foreign Key Wajib
                
                // Data Medis (Acak dalam batas wajar)
                'sistol'         => rand(110, 150),
                'diastol'        => rand(70, 100),
                'nadi'           => rand(60, 100),
                'gd_puasa'       => rand(80, 120),
                'gd_duajam'      => rand(100, 160),
                'gd_sewaktu'     => rand(100, 200),
                'asam_urat'      => rand(30, 80) / 10, // Hasil float: 3.0 - 8.0
                'chol'           => rand(150, 250),
                'tg'             => rand(100, 200),
                'suhu'           => rand(360, 385) / 10, // Hasil float: 36.0 - 38.5
                'berat'          => rand(50, 90),
                'tinggi'         => rand(155, 180),

                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        // 3. Insert ke database
        DB::table('pemeriksaan')->insert($data_pemeriksaan);
    }
}