<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Clock\now;

class SaranSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('saran')->insert([
            [
                'id_saran' => 'SRN-001',
                'kategori_saran' => 'Sehat',
                'saran' => "Selalu jaga kesehatan dengan Pola makan yang baik, bergizi dan seimbang, Minum air putih yang cukup, Istirahat yang cukup, Olahraga rutin, Menjaga Kebersihan dan Hindari paparan asap rokok.",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-002',
                'kategori_saran' => 'TD ↓',
                'saran' => "Banyak minum air putih, sesekali minum kopi, Konsumsi makanan yang mengandung garam, Olah raga rutin",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-003',
                'kategori_saran' => 'TD ↓ GD ↑',
                'saran' => "Banyak minum air putih, sesekali minum kopi, Konsumsi makanan yang mengandung garam, Olah raga rutin, Hindari makanan berkarbohidrat tinggi, Selalu rutin berobat, Taati anjuran dari dokter untuk minum obat",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-004',
                'kategori_saran' => 'TD ↓ GD AS ↑',
                'saran' => "Banyak minum air putih, sesekali minum kopi, Konsumsi makanan yang mengandung garam, Olah raga rutin, Hindari makanan berkarbohidrat tinggi, Selalu rutin berobat, Taati anjuran dari dokter untuk minum obat, Hindari makanan mengandung purin, Olah raga cukup",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-005',
                'kategori_saran' => 'TD ↓ GD CHOL↑',
                'saran' => "Banyak minum air putih, sesekali minum kopi, Konsumsi makanan yang mengandung garam, Olah raga rutin, Hindari makanan berkarbohidrat tinggi, Selalu rutin berobat, Taati anjuran dari dokter untuk minum obat, Olah raga cukup, Hindari makanan mengandung lemak tinggi, Banyakin makanan berserat tinggi",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-006',
                'kategori_saran' => 'TD ↓ GD AS CHOL↑',
                'saran' => "Banyak minum air putih, sesekali minum kopi, Konsumsi makanan yang mengandung garam, Olah raga rutin, Hindari makanan berkarbohidrat tinggi, Selalu rutin berobat, Taati anjuran dari dokter untuk minum obat, Hindari makanan mengandung purin, Olah raga cukup, Hindari makanan mengandung lemak tinggi, Banyakin makanan berserat tinggi",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-007',
                'kategori_saran' => 'TD ↓ AS ↑',
                'saran' => "Banyak minum air putih, sesekali minum kopi, Konsumsi makanan yang mengandung garam, Olah raga rutin, Hindari makanan mengandung purin, Selalu rutin kontrol",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-008',
                'kategori_saran' => 'TD ↓ AS CHOL ↑',
                'saran' => "Banyak minum air putih, sesekali minum kopi, Konsumsi makanan yang mengandung garam, Olah raga rutin, Hindari makanan mengandung purin, Selalu rutin kontrol, Hindari makanan mengandung lemak tinggi, Banyakin makanan berserat tinggi",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-009',
                'kategori_saran' => 'TD ↓ CHOL ↑',
                'saran' => "Banyak minum air putih, sesekali minum kopi, Konsumsi makanan yang mengandung garam, Olah raga rutin, Hindari makanan mengandung lemak tinggi, Banyakin makanan berserat tinggi, Selalu rutin kontrol",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-010',
                'kategori_saran' => 'TD ↑',
                'saran' => "Hindari makanan yang mengandung natrium, Istirahat cukup, Rutin minum obat, Rutin kontrol Dokter, Hindari makanan berkolesterol tinggi",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-011',
                'kategori_saran' => 'TD GD ↑',
                'saran' => "Hindari makanan yang mengandung natrium, Istirahat cukup, Rutin minum obat, Rutin kontrol Dokter, Hindari makanan berkolesterol tinggi, Hindari makanan berkarbohidrat tinggi",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-012',
                'kategori_saran' => 'TD GD AS ↑',
                'saran' => "Hindari makanan yang mengandung natrium, Istirahat cukup, Rutin minum obat, Rutin kontrol Dokter, Hindari makanan berkolesterol tinggi, Hindari makanan mengandung purin, Olah raga cukup",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-013',
                'kategori_saran' => 'TD GD CHOL ↑',
                'saran' => "Hindari makanan yang mengandung natrium, Istirahat cukup, Rutin minum obat, Rutin kontrol Dokter, Hindari makanan berkolesterol tinggi, Olah raga cukup, Banyakin makanan berserat tinggi",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-014',
                'kategori_saran' => 'TD GD AS CHOL ↑',
                'saran' => "Hindari makanan yang mengandung natrium, Istirahat cukup, Rutin minum obat, Rutin kontrol Dokter, Hindari makanan berkolesterol tinggi, Hindari makanan mengandung purin, Olah raga cukup, Banyakin makanan berserat tinggi",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-015',
                'kategori_saran' => 'TD AS ↑',
                'saran' => "Hindari makanan yang mengandung natrium, Istirahat cukup, Rutin minum obat, Rutin kontrol Dokter, Hindari makanan berkolesterol tinggi, Hindari makanan mengandung purin, Olah raga cukup",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-016',
                'kategori_saran' => 'TD AS CHOL ↑',
                'saran' => "Hindari makanan yang mengandung natrium, Istirahat cukup, Rutin minum obat, Rutin kontrol Dokter, Hindari makanan berkolesterol tinggi, Hindari makanan mengandung purin, Olah raga cukup, Banyakin makanan berserat tinggi",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-017',
                'kategori_saran' => 'TD CHOL ↑',
                'saran' => "Hindari makanan yang mengandung natrium, Istirahat cukup, Rutin minum obat, Rutin kontrol Dokter, Hindari makanan berkolesterol tinggi, Banyakin makanan berserat tinggi",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-018',
                'kategori_saran' => 'GD ↑',
                'saran' => "Hindari makanan berkarbohidrat tinggi, Selalu rutin berobat, Taati anjuran dari dokter untuk minum obat,",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-019',
                'kategori_saran' => 'GD AS ↑',
                'saran' => "Hindari makanan berkarbohidrat tinggi, Selalu rutin berobat, Taati anjuran dari dokter untuk minum obat, Hindari makanan mengandung purin, Olah raga cukup",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-020',
                'kategori_saran' => 'GD AS CHOL↑',
                'saran' => "Hindari makanan berkarbohidrat tinggi, Selalu rutin berobat, Taati anjuran dari dokter untuk minum obat, Hindari makanan mengandung purin, Olah raga cukup, Hindari makanan mengandung lemak tinggi, Banyakin makanan berserat tinggi",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-021',
                'kategori_saran' => 'GD CHOL↑',
                'saran' => "Hindari makanan berkarbohidrat tinggi, Selalu rutin berobat, Taati anjuran dari dokter untuk minum obat, Hindari makanan mengandung lemak tinggi, Banyakin makanan berserat tinggi",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-022',
                'kategori_saran' => 'AS ↑',
                'saran' => "Hindari makanan mengandung purin, Olah raga cukup, Selalu rutin kontrol",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-023',
                'kategori_saran' => 'AS CHOL↑',
                'saran' => "Hindari makanan mengandung purin, Olah raga cukup, Selalu rutin kontrol, Hindari makanan mengandung lemak tinggi, Banyakin makanan berserat tinggi",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_saran' => 'SRN-024',
                'kategori_saran' => 'CHOL ↑',
                'saran' => "Hindari makanan mengandung lemak tinggi, Banyakin makanan berserat tinggi, Selalu rutin kontrol",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
