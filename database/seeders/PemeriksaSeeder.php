<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PemeriksaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pemeriksa')->insert([
            [
                'id_pemeriksa' => 'PMR001',
                'nama_pemeriksa' => 'Sofia Meta Yustika',
                'status' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
