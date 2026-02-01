<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaranSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data_saran = [
            // ==========================
            // 1. KONDISI UMUM / SEHAT
            // ==========================
            [
                'id_saran' => 'SRN-NORM-01',
                'saran'    => 'Hasil pemeriksaan Anda dalam batas normal. Pertahankan pola hidup sehat, makan bergizi seimbang, dan olahraga rutin minimal 30 menit sehari.',
            ],

            // ==========================
            // 2. TEKANAN DARAH (Sistol/Diastol)
            // ==========================
            // Hipertensi (Tinggi) -> Sistol > 140 atau Diastol > 90
            [
                'id_saran' => 'SRN-TENS-01',
                'saran'    => 'Tekanan darah Anda tinggi. Kurangi konsumsi garam/asinan, hindari stres berlebih, kopi, dan rokok. Istirahat yang cukup.',
            ],
            // Hipotensi (Rendah) -> Sistol < 90
            [
                'id_saran' => 'SRN-TENS-02',
                'saran'    => 'Tekanan darah Anda rendah. Perbanyak minum air putih, konsumsi makanan bergizi (sayur bayam/ati ayam), dan jangan begadang.',
            ],

            // ==========================
            // 3. GULA DARAH (Puasa / 2 Jam / Sewaktu)
            // ==========================
            // Hiperglikemia (Diabetes)
            [
                'id_saran' => 'SRN-GULA-01',
                'saran'    => 'Gula darah terdeteksi tinggi. Batasi asupan nasi putih, tepung, dan gula. Ganti dengan karbohidrat kompleks (beras merah/ubi) dan rutin olahraga.',
            ],
            // Hipoglikemia (Rendah)
            [
                'id_saran' => 'SRN-GULA-02',
                'saran'    => 'Gula darah Anda terlalu rendah. Segera konsumsi makanan/minuman manis jika merasa pusing, dan jaga jadwal makan agar teratur.',
            ],

            // ==========================
            // 4. ASAM URAT
            // ==========================
            // Tinggi (> 7.0 mg/dL untuk Laki, > 6.0 untuk Wanita)
            [
                'id_saran' => 'SRN-ASAM-01',
                'saran'    => 'Asam urat tinggi. Hindari jeroan, emping, kacang-kacangan, makanan laut (seafood), dan sayuran hijau tertentu (bayam/kangkung). Perbanyak minum air putih.',
            ],

            // ==========================
            // 5. LEMAK DARAH (Kolesterol & Trigliserida)
            // ==========================
            // Kolesterol Tinggi (> 200 mg/dL)
            [
                'id_saran' => 'SRN-LEMK-01',
                'saran'    => 'Kolesterol Anda tinggi. Hindari gorengan, santan, kulit ayam, dan daging berlemak. Perbanyak makan ikan, buah, dan sayuran berserat tinggi.',
            ],
            // Trigliserida Tinggi (> 150 mg/dL)
            [
                'id_saran' => 'SRN-LEMK-02',
                'saran'    => 'Trigliserida tinggi. Kurangi makanan manis, tepung-tepungan, dan santan. Tingkatkan aktivitas fisik/kardio untuk membakar lemak.',
            ],

            // ==========================
            // 6. BERAT BADAN (IMT)
            // ==========================
            // Obesitas / Gemuk
            [
                'id_saran' => 'SRN-BDAN-01',
                'saran'    => 'Berat badan berlebih (Overweight/Obesitas). Disarankan diet rendah kalori, kurangi porsi makan malam, dan rutin berolahraga untuk menurunkan berat badan ideal.',
            ],
            // Kurus (Underweight)
            [
                'id_saran' => 'SRN-BDAN-02',
                'saran'    => 'Berat badan kurang. Tingkatkan asupan kalori dan protein (telur, susu, daging). Makan lebih sering dengan porsi kecil tapi padat gizi.',
            ],

            // ==========================
            // 7. SUHU TUBUH & NADI
            // ==========================
            // Demam (> 37.5)
            [
                'id_saran' => 'SRN-UMUM-01',
                'saran'    => 'Suhu tubuh tinggi (Demam). Perbanyak minum air putih, kompres hangat, dan istirahat total. Segera minum obat penurun panas jika perlu.',
            ],
            // Takikardia (Nadi Cepat > 100)
            [
                'id_saran' => 'SRN-UMUM-02',
                'saran'    => 'Denyut nadi terdeteksi cepat. Hindari kafein (kopi/teh pekat), kelola kecemasan, dan periksa ke dokter jantung jika disertai sesak/nyeri dada.',
            ],
        ];

        // Tambah Timestamps & Insert
        foreach ($data_saran as &$item) {
            $item['is_active']  = true;
            $item['created_at'] = $now;
            $item['updated_at'] = $now;
        }

        DB::table('saran')->upsert(
            $data_saran,
            ['id_saran'],
            ['saran', 'is_active', 'updated_at']
        );
    }
}