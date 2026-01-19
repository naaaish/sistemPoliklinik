<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Wajib import ini
use Carbon\Carbon; // Wajib import ini untuk timestamps

class DiagnosaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $data_diagnosa = [
            ['id_diagnosa' => 'DG-001', 'diagnosa' => 'Influenza (Flu)'],
            ['id_diagnosa' => 'DG-002', 'diagnosa' => 'Hipertensi (Tekanan Darah Tinggi)'],
            ['id_diagnosa' => 'DG-003', 'diagnosa' => 'Diabetes Melitus Tipe 2'],
            ['id_diagnosa' => 'DG-004', 'diagnosa' => 'Gastritis (Maag)'],
            ['id_diagnosa' => 'DG-005', 'diagnosa' => 'Demam Tifoid (Tipes)'],
            ['id_diagnosa' => 'DG-006', 'diagnosa' => 'Infeksi Saluran Pernapasan Akut (ISPA)'],
            ['id_diagnosa' => 'DG-007', 'diagnosa' => 'Demam Berdarah Dengue (DBD)'],
            ['id_diagnosa' => 'DG-008', 'diagnosa' => 'Diare Akut'],
            ['id_diagnosa' => 'DG-009', 'diagnosa' => 'Asma Bronkial'],
            ['id_diagnosa' => 'DG-010', 'diagnosa' => 'Dermatitis Alergi (Gatal-gatal)'],
            ['id_diagnosa' => 'DG-011', 'diagnosa' => 'Dispepsia (Gangguan Pencernaan)'],
            ['id_diagnosa' => 'DG-012', 'diagnosa' => 'Cephalgia (Sakit Kepala/Migrain)'],
            ['id_diagnosa' => 'DG-013', 'diagnosa' => 'Anemia Defisiensi Besi'],
            ['id_diagnosa' => 'DG-014', 'diagnosa' => 'Vertigo'],
            ['id_diagnosa' => 'DG-015', 'diagnosa' => 'Faringitis Akut (Radang Tenggorokan)'],
        ];

        // Loop data untuk menambahkan timestamps otomatis
        foreach ($data_diagnosa as &$data) {
            $data['is_active'] = true;
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
        }

        // Insert ke database
        DB::table('diagnosa')->insert($data_diagnosa);
    }
}