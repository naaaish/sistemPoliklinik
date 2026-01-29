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

        // Data saran disesuaikan dengan DiagnosaSeeder (DG-001 s/d DG-040)
        $data_saran = [
            // --- SALURAN PERNAFASAN ---
            ['id_saran' => 'SRN-001', 'saran' => 'Istirahat total, banyak minum air hangat, dan konsumsi vitamin C.', 'is_active' => true],
            ['id_saran' => 'SRN-002', 'saran' => 'Hindari gorengan/es, sering kumur air garam hangat.', 'is_active' => true],
            ['id_saran' => 'SRN-003', 'saran' => 'Istirahatkan suara (jangan banyak bicara), minum air jahe.', 'is_active' => true],
            ['id_saran' => 'SRN-004', 'saran' => 'Hindari asap rokok/debu, gunakan masker, perbanyak air putih.', 'is_active' => true],
            ['id_saran' => 'SRN-005', 'saran' => 'Lakukan irigasi hidung dengan larutan saline, kompres hangat di wajah.', 'is_active' => true],
            ['id_saran' => 'SRN-006', 'saran' => 'Hindari pemicu alergi (debu/dingin), jaga kebersihan rumah.', 'is_active' => true],
            ['id_saran' => 'SRN-007', 'saran' => 'Istirahat cukup, hindari polusi udara, minum obat pengencer dahak.', 'is_active' => true],
            ['id_saran' => 'SRN-008', 'saran' => 'Segera rujuk ke spesialis paru, istirahat total.', 'is_active' => true],
            ['id_saran' => 'SRN-009', 'saran' => 'Habiskan antibiotik sesuai resep dokter, bed rest total.', 'is_active' => true],
            ['id_saran' => 'SRN-010', 'saran' => 'Selalu sedia inhaler, hindari aktivitas fisik berat saat kambuh.', 'is_active' => true],
            ['id_saran' => 'SRN-011', 'saran' => 'Minum obat rutin tanpa putus selama 6 bulan, pakai masker, ventilasi rumah harus bagus.', 'is_active' => true],
            ['id_saran' => 'SRN-012', 'saran' => 'Minum antibiotik jika diresepkan, kumur antiseptik.', 'is_active' => true],

            // --- SALURAN PENCERNAAN ---
            ['id_saran' => 'SRN-013', 'saran' => 'Makan sedikit tapi sering, hindari pedas/asam/kopi.', 'is_active' => true],
            ['id_saran' => 'SRN-014', 'saran' => 'Rehidrasi segera dengan oralit/infus, jaga kebersihan air minum.', 'is_active' => true],
            ['id_saran' => 'SRN-015', 'saran' => 'Minum oralit setiap BAB, makan makanan rendah serat sementara.', 'is_active' => true],
            ['id_saran' => 'SRN-016', 'saran' => 'Jaga kebersihan tangan, minum antibiotik sesuai anjuran.', 'is_active' => true],
            ['id_saran' => 'SRN-017', 'saran' => 'Bed rest total, makan bubur halus, hindari jajanan luar.', 'is_active' => true],
            ['id_saran' => 'SRN-018', 'saran' => 'Istirahat total, jaga kebersihan makanan.', 'is_active' => true],
            ['id_saran' => 'SRN-019', 'saran' => 'Hindari alkohol dan makanan berlemak, istirahat cukup.', 'is_active' => true],
            ['id_saran' => 'SRN-020', 'saran' => 'Jangan langsung berbaring setelah makan, kunyah makanan perlahan.', 'is_active' => true],
            ['id_saran' => 'SRN-021', 'saran' => 'Tahan napas sebentar, minum air dingin perlahan.', 'is_active' => true],
            ['id_saran' => 'SRN-022', 'saran' => 'Minum obat cacing rutin 6 bulan sekali, potong kuku pendek.', 'is_active' => true],
            ['id_saran' => 'SRN-023', 'saran' => 'Kompres hangat pada perut, hindari makanan gas (kol/ubi).', 'is_active' => true],
            ['id_saran' => 'SRN-024', 'saran' => 'Perbanyak makan sayur/buah (serat), banyak minum air putih.', 'is_active' => true],

            // --- GINJAL & KEMIH ---
            ['id_saran' => 'SRN-025', 'saran' => 'Jangan menahan buang air kecil, perbanyak minum air putih, jaga kebersihan area intim.', 'is_active' => true],
            ['id_saran' => 'SRN-026', 'saran' => 'Minum minimal 2-3 liter air per hari, kurangi konsumsi garam dan jeroan.', 'is_active' => true],

            // --- JANTUNG & DARAH ---
            ['id_saran' => 'SRN-027', 'saran' => 'Kurangi garam, rutin cek tensi, kelola stres.', 'is_active' => true],
            ['id_saran' => 'SRN-028', 'saran' => 'Minum yang cukup, bangun dari tidur secara perlahan, olahraga ringan.', 'is_active' => true],
            ['id_saran' => 'SRN-029', 'saran' => 'Pola makan rendah kolesterol, rutin olahraga kardio ringan, stop merokok.', 'is_active' => true],
            // --- PEMBULUH DARAH ---
            ['id_saran' => 'SRN-030', 'saran' => 'Perbanyak serat agar BAB lunak, jangan mengejan terlalu kuat.', 'is_active' => true],
            ['id_saran' => 'SRN-031', 'saran' => 'Gunakan stoking kompresi, angkat kaki lebih tinggi saat tidur.', 'is_active' => true],
            ['id_saran' => 'SRN-032', 'saran' => 'Kompres hangat pada area bengkak, istirahatkan lengan/kaki.', 'is_active' => true],
            ['id_saran' => 'SRN-033', 'saran' => 'Perbanyak cairan elektrolit/jus jambu, pantau suhu tubuh tiap 4 jam.', 'is_active' => true],

            // --- KELAINAN DARAH ---
            ['id_saran' => 'SRN-034', 'saran' => 'Konsumsi suplemen zat besi, makan daging merah/bayam/ati ayam.', 'is_active' => true],

            // --- OTOT & RANGKA ---
            ['id_saran' => 'SRN-035', 'saran' => 'Istirahatkan otot yang sakit, kompres hangat, pijat ringan.', 'is_active' => true],
            ['id_saran' => 'SRN-036', 'saran' => 'Hindari aktivitas berat pada sendi, kompres dingin jika bengkak.', 'is_active' => true],
            ['id_saran' => 'SRN-037', 'saran' => 'Latihan gerak sendi ringan, rutin minum obat antiradang dari dokter.', 'is_active' => true],
            ['id_saran' => 'SRN-038', 'saran' => 'Hindari emping, jeroan, kacang-kacangan, dan makanan laut.', 'is_active' => true],
            ['id_saran' => 'SRN-039', 'saran' => 'Jaga postur tubuh tegak, hindari mengangkat beban berat membungkuk, fisioterapi.', 'is_active' => true],
            ['id_saran' => 'SRN-040', 'saran' => 'Kurangi berat badan ideal, olahraga berenang atau bersepeda statis.', 'is_active' => true],
        ];

        // Tambah Timestamps
        foreach ($data_saran as &$data) {
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
        }

        // Gunakan upsert agar aman
        DB::table('saran')->upsert(
            $data_saran,
            ['id_saran'], // Unique key
            ['saran', 'is_active', 'updated_at'] // Kolom yang diupdate jika id sama
        );
    }
}