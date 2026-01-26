<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DiagnosaSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data_diagnosa = [
            // ==========================================
            // [1] SALURAN PERNAFASAN
            // ==========================================
            // 1.1: Infeksi saluran pernafasan atas (Flu, Tonsilitis, Pharingitis, Laringitis, Sinusitis, Rhinitis)
            ['id_diagnosa' => 'DG-001', 'id_nb' => '1.1', 'diagnosa' => 'Influenza (Flu)'],
            ['id_diagnosa' => 'DG-002', 'id_nb' => '1.1', 'diagnosa' => 'Tonsilitis'],
            ['id_diagnosa' => 'DG-003', 'id_nb' => '1.1', 'diagnosa' => 'Pharingitis'],
            ['id_diagnosa' => 'DG-004', 'id_nb' => '1.1', 'diagnosa' => 'Laringitis'],
            ['id_diagnosa' => 'DG-005', 'id_nb' => '1.1', 'diagnosa' => 'Sinusitis'],
            ['id_diagnosa' => 'DG-006', 'id_nb' => '1.1', 'diagnosa' => 'Rhinitis'],

            // 1.2: Infeksi saluran pernafasan lain (Bronchitis, Pleuritis, Pneumonia, Asma)
            ['id_diagnosa' => 'DG-007', 'id_nb' => '1.2', 'diagnosa' => 'Bronchitis'],
            ['id_diagnosa' => 'DG-008', 'id_nb' => '1.2', 'diagnosa' => 'Pleuritis'],
            ['id_diagnosa' => 'DG-009', 'id_nb' => '1.2', 'diagnosa' => 'Pneumonia'],
            ['id_diagnosa' => 'DG-010', 'id_nb' => '1.2', 'diagnosa' => 'Asma Bronkial'],

            // 1.3: Tuberkulosa
            ['id_diagnosa' => 'DG-011', 'id_nb' => '1.3', 'diagnosa' => 'Tuberkulosa (TB)'],

            // 1.4: Tonsilo Faringitis
            ['id_diagnosa' => 'DG-012', 'id_nb' => '1.4', 'diagnosa' => 'Tonsilo Faringitis'],

            // ==========================================
            // [2] SALURAN PENCERNAAN
            // ==========================================
            // 2.1: Gastritis
            ['id_diagnosa' => 'DG-013', 'id_nb' => '2.1', 'diagnosa' => 'Gastritis / Tukak Lambung (Maag)'],
            
            // 2.2: Kolera
            ['id_diagnosa' => 'DG-014', 'id_nb' => '2.2', 'diagnosa' => 'Kolera'],

            // 2.3: Diare, Dysentri
            ['id_diagnosa' => 'DG-015', 'id_nb' => '2.3', 'diagnosa' => 'Diare Akut'],
            ['id_diagnosa' => 'DG-016', 'id_nb' => '2.3', 'diagnosa' => 'Dysentri'],

            // 2.4: Typus
            ['id_diagnosa' => 'DG-017', 'id_nb' => '2.4', 'diagnosa' => 'Demam Tifoid (Tipes)'],
            ['id_diagnosa' => 'DG-018', 'id_nb' => '2.4', 'diagnosa' => 'Paratyphus'],

            // 2.5: Radang Hati
            ['id_diagnosa' => 'DG-019', 'id_nb' => '2.5', 'diagnosa' => 'Hepatitis (Radang Hati)'],

            // 2.6: Dispepsi
            ['id_diagnosa' => 'DG-020', 'id_nb' => '2.6', 'diagnosa' => 'Dispepsia'],

            // 2.7: Hycoup
            ['id_diagnosa' => 'DG-021', 'id_nb' => '2.7', 'diagnosa' => 'Hiccup (Cegukan Terus-menerus)'],

            // 2.8: Ascariasis
            ['id_diagnosa' => 'DG-022', 'id_nb' => '2.8', 'diagnosa' => 'Ascariasis (Cacingan)'],

            // 2.9: Kolik Abdomen
            ['id_diagnosa' => 'DG-023', 'id_nb' => '2.9', 'diagnosa' => 'Kolik Abdomen (Nyeri Perut Hebat)'],

            // 2.10: Konstipasi
            ['id_diagnosa' => 'DG-024', 'id_nb' => '2.10','diagnosa' => 'Konstipasi (Sembelit)'],

            // ==========================================
            // [3] GINJAL DAN SALURAN KEMIH
            // ==========================================
            ['id_diagnosa' => 'DG-025', 'id_nb' => '3.1', 'diagnosa' => 'Infeksi Saluran Kemih (ISK)'],
            ['id_diagnosa' => 'DG-026', 'id_nb' => '3.2', 'diagnosa' => 'Batu Ginjal / Saluran Kencing'],

            // ==========================================
            // [4] JANTUNG & TEKANAN DARAH
            // ==========================================
            ['id_diagnosa' => 'DG-027', 'id_nb' => '4.1', 'diagnosa' => 'Hipertensi'],
            ['id_diagnosa' => 'DG-028', 'id_nb' => '4.2', 'diagnosa' => 'Hipotensi'],
            ['id_diagnosa' => 'DG-029', 'id_nb' => '4.3', 'diagnosa' => 'Penyakit Jantung Koroner'],

            // ==========================================
            // [5] KELAINAN PEMBULUH DARAH
            // ==========================================
            ['id_diagnosa' => 'DG-030', 'id_nb' => '5.1', 'diagnosa' => 'Hemoroid (Wasir)'],
            ['id_diagnosa' => 'DG-031', 'id_nb' => '5.2', 'diagnosa' => 'Varises'],
            ['id_diagnosa' => 'DG-032', 'id_nb' => '5.3', 'diagnosa' => 'Phlebitis'],
            ['id_diagnosa' => 'DG-033', 'id_nb' => '5.4', 'diagnosa' => 'Demam Berdarah Dengue (DBD)'],

            // ==========================================
            // [6] KELAINAN DARAH
            // ==========================================
            ['id_diagnosa' => 'DG-034', 'id_nb' => '6.1', 'diagnosa' => 'Anemia'],

            // ==========================================
            // [7] OTOT DAN KERANGKA
            // ==========================================
            ['id_diagnosa' => 'DG-035', 'id_nb' => '7.1', 'diagnosa' => 'Myalgia (Nyeri Otot)'],
            ['id_diagnosa' => 'DG-036', 'id_nb' => '7.1', 'diagnosa' => 'Arthralgia (Nyeri Sendi)'],
            ['id_diagnosa' => 'DG-037', 'id_nb' => '7.2', 'diagnosa' => 'Arthritis Rheumatoid'],
            ['id_diagnosa' => 'DG-038', 'id_nb' => '7.2', 'diagnosa' => 'Gout (Asam Urat)'],
            ['id_diagnosa' => 'DG-039', 'id_nb' => '7.3', 'diagnosa' => 'HNP (Saraf Kejepit)'],
            ['id_diagnosa' => 'DG-040', 'id_nb' => '7.4', 'diagnosa' => 'Osteoarthritis'],
        ];

        // Tambah Timestamp & Active
        foreach ($data_diagnosa as &$data) {
            $data['is_active'] = true;
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
        }

        // Pakai Upsert biar aman kalau di-seed ulang
        DB::table('diagnosa')->upsert(
            $data_diagnosa,
            ['id_diagnosa'],
            ['diagnosa', 'id_nb', 'is_active', 'updated_at']
        );
    }
}