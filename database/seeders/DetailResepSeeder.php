<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailResepSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil harga obat dari database agar akurat
        $harga = DB::table('obat')->pluck('harga', 'id_obat');

        // Pastikan obat ada
        if ($harga->isEmpty()) { 
            $this->command->warn('Tabel Obat kosong!'); return; 
        }

        $detail = [
            // --- RESEP 1 (Pegawai - Hipertensi & Pusing) ---
            // 1 Strip Paracetamol (OBT-001)
            [
                'id_resep' => 'RSP-001',
                'id_obat'  => 'OBT-001',
                'jumlah'   => 1,
                'satuan'   => 'Strip',
                'subtotal' => 1 * ($harga['OBT-001'] ?? 5000),
            ],
            // 1 Strip Vitamin C (OBT-003)
            [
                'id_resep' => 'RSP-001',
                'id_obat'  => 'OBT-003',
                'jumlah'   => 1,
                'satuan'   => 'Strip',
                'subtotal' => 1 * ($harga['OBT-003'] ?? 45000),
            ],

            // --- RESEP 2 (Anak - Flu & Demam) ---
            // 1 Botol OBH (OBT-004)
            [
                'id_resep' => 'RSP-002',
                'id_obat'  => 'OBT-004',
                'jumlah'   => 1,
                'satuan'   => 'Botol',
                'subtotal' => 1 * ($harga['OBT-004'] ?? 18000),
            ],
            // 5 Tablet Amoxicillin (OBT-002) - Antibiotik
            [
                'id_resep' => 'RSP-002',
                'id_obat'  => 'OBT-002',
                'jumlah'   => 5,
                'satuan'   => 'Tablet',
                'subtotal' => 5 * ($harga['OBT-002'] ?? 12500),
            ],
        ];

        DB::table('detail_resep')->insert($detail);

        // UPDATE TOTAL TAGIHAN DI TABEL RESEP SECARA OTOMATIS
        $this->updateTotalTagihan();
    }

    private function updateTotalTagihan()
    {
        $reseps = DB::table('resep')->get();
        foreach($reseps as $resep) {
            $total = DB::table('detail_resep')
                       ->where('id_resep', $resep->id_resep)
                       ->sum('subtotal');
            
            DB::table('resep')
                ->where('id_resep', $resep->id_resep)
                ->update(['total_tagihan' => $total]);
        }
    }
}