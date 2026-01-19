<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResepSeeder extends Seeder
{
    public function run(): void
    {
        // ===============================
        // RESEP
        // ===============================
        DB::table('resep')->insert([
            'id_resep' => 'RSP-001',
            'id_pemeriksaan' => 'PMX-001',
            'total_tagihan' => 62500,
        ]);

        // Ambil harga obat
        $obat = DB::table('obat')
            ->whereIn('id_obat', ['OBT-001','OBT-002','OBT-004'])
            ->pluck('harga','id_obat');
            
        DB::table('detail_resep')->insert([
            [
                'id_resep' => 'RSP-001',
                'id_obat'  => 'OBT-001',
                'jumlah'   => 2,
                'satuan'   => 'tablet',
                'subtotal' => 2 * $obat['OBT-001'],
            ],
            [
                'id_resep' => 'RSP-001',
                'id_obat'  => 'OBT-002',
                'jumlah'   => 4,
                'satuan'   => 'tablet',
                'subtotal' => 4 * $obat['OBT-002'],
            ],
            [
                'id_resep' => 'RSP-001',
                'id_obat'  => 'OBT-004',
                'jumlah'   => 3,
                'satuan'   => 'botol',
                'subtotal' => 3 * $obat['OBT-004'],
            ],
        ]);
    }
}
