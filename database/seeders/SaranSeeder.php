<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $data_saran = [
            // DG-001: Influenza
            [
                'id_saran'    => 'SRN-001',
                'id_diagnosa' => 'DG-001', 
                'saran'       => 'Istirahat total minimal 3 hari, perbanyak minum air hangat, dan konsumsi vitamin C.'
            ],
            // DG-002: Hipertensi
            [
                'id_saran'    => 'SRN-002',
                'id_diagnosa' => 'DG-002', 
                'saran'       => 'Kurangi konsumsi garam/asinan, hindari kopi/rokok, dan kelola stres dengan baik.'
            ],
            // DG-003: Diabetes
            [
                'id_saran'    => 'SRN-003',
                'id_diagnosa' => 'DG-003', 
                'saran'       => 'Batasi asupan gula dan karbohidrat, rutin olahraga ringan, dan periksa gula darah berkala.'
            ],
            // DG-004: Gastritis (Maag)
            [
                'id_saran'    => 'SRN-004',
                'id_diagnosa' => 'DG-004', 
                'saran'       => 'Makan teratur (sedikit tapi sering), hindari makanan pedas, asam, dan bersantan.'
            ],
            // DG-005: Tipes
            [
                'id_saran'    => 'SRN-005',
                'id_diagnosa' => 'DG-005', 
                'saran'       => 'Bed rest total, konsumsi makanan lunak (bubur), dan hindari jajanan tidak higienis.'
            ],
            // DG-006: ISPA
            [
                'id_saran'    => 'SRN-006',
                'id_diagnosa' => 'DG-006', 
                'saran'       => 'Gunakan masker, hindari debu/asap rokok, dan jaga ventilasi udara di kamar.'
            ],
            // DG-007: DBD
            [
                'id_saran'    => 'SRN-007',
                'id_diagnosa' => 'DG-007', 
                'saran'       => 'Minum cairan elektrolit/jus jambu yang banyak untuk menaikkan trombosit, pantau suhu tubuh.'
            ],
            // DG-008: Diare
            [
                'id_saran'    => 'SRN-008',
                'id_diagnosa' => 'DG-008', 
                'saran'       => 'Minum oralit setiap buang air besar, hindari susu dan makanan berserat tinggi sementara waktu.'
            ],
            // DG-009: Asma
            [
                'id_saran'    => 'SRN-009',
                'id_diagnosa' => 'DG-009', 
                'saran'       => 'Hindari pemicu alergi (debu/dingin), selalu sedia inhaler kemanapun pergi.'
            ],
            // DG-010: Dermatitis (Alergi Kulit)
            [
                'id_saran'    => 'SRN-010',
                'id_diagnosa' => 'DG-010', 
                'saran'       => 'Jangan digaruk agar tidak infeksi, gunakan sabun bayi/lembut, cari tahu pemicu alergi.'
            ],
            // DG-011: Dispepsia
            [
                'id_saran'    => 'SRN-011',
                'id_diagnosa' => 'DG-011', 
                'saran'       => 'Hindari makan terlalu cepat, jangan langsung berbaring setelah makan.'
            ],
            // DG-012: Sakit Kepala/Migrain
            [
                'id_saran'    => 'SRN-012',
                'id_diagnosa' => 'DG-012', 
                'saran'       => 'Istirahat di ruangan gelap dan hening, pijat area leher perlahan, perbaiki posisi tidur.'
            ],
            // DG-013: Anemia
            [
                'id_saran'    => 'SRN-013',
                'id_diagnosa' => 'DG-013', 
                'saran'       => 'Konsumsi makanan tinggi zat besi (bayam/daging merah), hindari minum teh setelah makan.'
            ],
            // DG-014: Vertigo
            [
                'id_saran'    => 'SRN-014',
                'id_diagnosa' => 'DG-014', 
                'saran'       => 'Hindari gerakan kepala mendadak, tidur dengan bantal agak tinggi.'
            ],
            // DG-015: Radang Tenggorokan
            [
                'id_saran'    => 'SRN-015',
                'id_diagnosa' => 'DG-015', 
                'saran'       => 'Sering kumur air garam hangat, hindari gorengan dan minuman dingin (es).'
            ],
        ];

        // Loop untuk tambah timestamp
        foreach ($data_saran as &$data) {
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
        }

        DB::table('saran')->insert($data_saran);
    }
}