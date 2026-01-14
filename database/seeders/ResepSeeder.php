<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil semua ID Pemeriksaan yang ada
        $list_pemeriksaan = DB::table('pemeriksaan')->pluck('id_pemeriksaan')->toArray();

        // Cek agar tidak error jika tabel pemeriksaan masih kosong
        if (empty($list_pemeriksaan)) {
            $this->command->warn("Tabel 'pemeriksaan' kosong. Harap jalankan PemeriksaanSeeder terlebih dahulu.");
            return;
        }

        $now = Carbon::now();
        $data_resep = [];

        // 2. Loop data pemeriksaan untuk dibuatkan tagihan resepnya
        foreach ($list_pemeriksaan as $index => $id_pmx) {
            
            // LOGIKA: Kita anggap 80% pemeriksaan menghasilkan resep obat.
            // Sisanya (20%) mungkin cuma konsultasi tanpa obat.
            if (rand(1, 100) <= 80) {
                
                // Generate ID Resep: RSP-001, RSP-002, dst.
                $id_resep = 'RSP-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

                // Generate Total Tagihan (Random kelipatan 5000)
                // Range: 15.000 s/d 250.000
                $tagihan = rand(3, 50) * 5000;

                $data_resep[] = [
                    'id_resep'       => $id_resep,
                    'total_tagihan'  => $tagihan,
                    'id_pemeriksaan' => $id_pmx,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
        }

        // 3. Masukkan ke database
        DB::table('resep')->insert($data_resep);
    }
}