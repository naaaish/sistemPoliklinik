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
                'nama_obat' => 'Acarbose 100mg',
                'harga'     => 3200.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-002',
                'nama_obat' => 'Acarbose 50mg',
                'harga'     => 1975.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-003',
                'nama_obat' => 'Adalat Oros',
                'harga'     => 16900.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
             [
                'id_obat'   => 'OBT-004',
                'nama_obat' => 'Alkohol Swab',
                'harga'     => 340.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-005',
                'nama_obat' => 'Alkohol 70 % 100 ml',
                'harga'     => 13600.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-006',
                'nama_obat' => 'Alloclair Gel',
                'harga'     => 160400.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ],
            [
                'id_obat'   => 'OBT-007',
                'nama_obat' => 'Allopurinol',
                'harga'     => 547.00,
                'is_active' => true,
                'created_at' => Carbon::now(),
            ]
        ];

        DB::table('obat')->insert($data_obat);
    }
}