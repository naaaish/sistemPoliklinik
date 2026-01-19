<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Import DB Facade
use Carbon\Carbon; // Import Carbon untuk tanggal

class ObatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // OPSI 1: Data Manual (Gunakan ini jika ingin data real)
        $data_obat = [
            [
                'id_obat'   => 'OBT-001',
                'nama_obat' => 'Paracetamol 500mg',
                'harga'     => 5000.00,
                'exp_date'  => '2026-12-31',
                'is_active' => true,
            ],
            [
                'id_obat'   => 'OBT-002',
                'nama_obat' => 'Amoxicillin 500mg',
                'harga'     => 12500.00,
                'exp_date'  => '2025-10-20',
                'is_active' => true,
            ],
            [
                'id_obat'   => 'OBT-003',
                'nama_obat' => 'Vitamin C 1000mg',
                'harga'     => 45000.00,
                'exp_date'  => '2027-01-15',
                'is_active' => true,
            ],
             [
                'id_obat'   => 'OBT-004',
                'nama_obat' => 'OBH Combi',
                'harga'     => 18000.00,
                'exp_date'  => '2026-06-05',
                'is_active' => true,
            ],
        ];

        // Masukkan data manual ke database
        DB::table('obat')->insert($data_obat);
    }
}