<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResepSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        
        // Buat Header Resep (Total Tagihan akan diupdate otomatis oleh DetailResepSeeder)
        DB::table('resep')->insertOrIgnore([
            [
                'id_resep'       => 'RSP-001', // Untuk Pegawai
                'id_pemeriksaan' => 'PMX-001',
                'total_tagihan'  => 0, // Nanti diupdate
            ],
            [
                'id_resep'       => 'RSP-002', // Untuk Anak
                'id_pemeriksaan' => 'PMX-002',
                'total_tagihan'  => 0, // Nanti diupdate
            ]
        ]);
    }
}