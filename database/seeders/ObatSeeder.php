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
        $data_obat = [
            [
                'id_obat'   => 'OBT-001',
                'nama_obat' => 'Paracetamol 500mg',
                'harga'     => 5000.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-002',
                'nama_obat' => 'Amoxicillin 500mg',
                'harga'     => 12500.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-003',
                'nama_obat' => 'Vitamin C 1000mg',
                'harga'     => 45000.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
             [
                'id_obat'   => 'OBT-004',
                'nama_obat' => 'OBH Combi',
                'harga'     => 18000.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-005',
                'nama_obat' => 'Loperamide 2mg',
                'harga'     => 8000.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-006',
                'nama_obat' => 'Cetirizine 10mg',
                'harga'     => 15000.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-007',
                'nama_obat' => 'Metformin 500mg',
                'harga'     => 20000.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-008',
                'nama_obat' => 'Entrostop',
                'harga'     => 9000.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            // alat kesehatan
            [
                'id_obat'   => 'OBT-009',
                'nama_obat' => 'Masker Medis 50pcs',
                'harga'     => 75000.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-010',
                'nama_obat' => 'Sarung Tangan Latex 100pcs',
                'harga'     => 120000.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-011',
                'nama_obat' => 'Alkohol 70% 500ml',
                'harga'     => 25000.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-012',
                'nama_obat' => 'Plester Luka 10pcs',
                'harga'     => 15000.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-013',
                'nama_obat' => 'Thermometer Digital',
                'harga'     => 85000.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-014',
                'nama_obat' => 'Tensimeter Manual',
                'harga'     => 150000.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ]
        ];

        // Masukkan data manual ke database
        DB::table('obat')->insert($data_obat);
    }
}