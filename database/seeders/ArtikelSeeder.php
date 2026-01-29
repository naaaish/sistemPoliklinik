<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArtikelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $data_artikel = [
            [
                'id_artikel'    => 'ART-001',
                'judul_artikel' => '5 Tips Menjaga Kesehatan Jantung di Usia Muda',
                'tanggal'       => '2025-01-10',
                'cover_path'    => 'artikel-cover/jantung-sehat.png', 
                'isi_artikel'   => 'Penyakit jantung tidak hanya menyerang orang tua. Anak muda pun berisiko jika pola hidup tidak dijaga. Berikut adalah 5 tips mudah: 1. Rutin olahraga kardio minimal 30 menit sehari. 2. Kurangi makanan berminyak dan bersantan. 3. Kelola stres dengan baik. 4. Tidur cukup 7-8 jam sehari. 5. Rutin cek tekanan darah.',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'id_artikel'    => 'ART-002',
                'judul_artikel' => 'Pentingnya Minum Air Putih 8 Gelas Sehari',
                'tanggal'       => '2025-01-12',
                'cover_path'    => 'artikel-cover/air-putih.png',
                'isi_artikel'   => 'Dehidrasi dapat menyebabkan penurunan konsentrasi, kulit kering, hingga gangguan ginjal. Pastikan Anda minum minimal 2 liter atau setara 8 gelas air putih setiap hari untuk menjaga metabolisme tubuh tetap optimal.',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'id_artikel'    => 'ART-003',
                'judul_artikel' => 'Mengenal Gejala Awal Diabetes Melitus',
                'tanggal'       => '2025-01-14',
                'cover_path'    => 'artikel-cover/diabetes.png',
                'isi_artikel'   => 'Diabetes sering disebut "Silent Killer". Waspadai gejala 3P: Poliuri (sering buang air kecil), Polidipsi (sering haus berlebih), dan Polifagi (sering lapar). Jika mengalami gejala ini disertai penurunan berat badan drastis, segera periksakan ke dokter.',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'id_artikel'    => 'ART-004',
                'judul_artikel' => 'Manfaat Vitamin C untuk Daya Tahan Tubuh',
                'tanggal'       => '2025-01-15',
                'cover_path'    => 'artikel-cover/vitamin-c.png', 
                'isi_artikel'   => 'Vitamin C adalah antioksidan kuat yang membantu melindungi sel tubuh dari kerusakan radikal bebas. Sumber alami vitamin C bisa didapat dari jeruk, jambu biji, kiwi, dan paprika.',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'id_artikel'    => 'ART-005',
                'judul_artikel' => 'Bahaya Duduk Terlalu Lama Bagi Pekerja Kantoran',
                'tanggal'       => '2025-01-16',
                'cover_path'    => 'artikel-cover/backpain.png',
                'isi_artikel'   => 'Duduk lebih dari 4 jam terus menerus dapat meningkatkan risiko sakit pinggang, obesitas, dan penyakit kardiovaskular. Lakukan peregangan ringan setiap 1 jam sekali untuk melancarkan aliran darah.',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ];

        DB::table('artikel')->insert($data_artikel);
    }
}