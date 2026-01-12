<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dokter;
use App\Models\JadwalDokter;
use App\Models\Artikel;

class DashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Dokter
        $dokter1 = Dokter::create([
            'nama' => 'Dr. Aulia Putri',
            'spesialisasi' => 'Spesialis Anak',
            'foto' => 'default.jpg',
            'no_telepon' => '08123456789',
            'email' => 'aulia@poliklinik.com'
        ]);

        $dokter2 = Dokter::create([
            'nama' => 'Dr. Budi Santoso',
            'spesialisasi' => 'Spesialis Umum',
            'foto' => 'default.jpg',
            'no_telepon' => '08234567890',
            'email' => 'budi@poliklinik.com'
        ]);

        $dokter3 = Dokter::create([
            'nama' => 'Dr. Citra Dewi',
            'spesialisasi' => 'Spesialis Penyakit Dalam',
            'foto' => 'default.jpg',
            'no_telepon' => '08345678901',
            'email' => 'citra@poliklinik.com'
        ]);

        // Seed Jadwal Dokter
        JadwalDokter::create([
            'dokter_id' => $dokter1->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '10:00:00'
        ]);

        JadwalDokter::create([
            'dokter_id' => $dokter1->id,
            'hari' => 'Rabu',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '10:00:00'
        ]);

        JadwalDokter::create([
            'dokter_id' => $dokter2->id,
            'hari' => 'Selasa',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '11:00:00'
        ]);

        JadwalDokter::create([
            'dokter_id' => $dokter2->id,
            'hari' => 'Kamis',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '11:00:00'
        ]);

        JadwalDokter::create([
            'dokter_id' => $dokter3->id,
            'hari' => 'Senin',
            'jam_mulai' => '13:00:00',
            'jam_selesai' => '15:00:00'
        ]);

        JadwalDokter::create([
            'dokter_id' => $dokter3->id,
            'hari' => 'Jumat',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '12:00:00'
        ]);

        // Seed Artikel
        Artikel::create([
            'judul' => 'Apa Bedanya Superflu dengan Flu Biasa?',
            'konten' => 'Superflu atau influenza berat adalah kondisi yang lebih serius dibandingkan flu biasa. Gejala superflu biasanya lebih parah dan berlangsung lebih lama. Penting untuk mengenali perbedaannya agar bisa mendapat penanganan yang tepat.',
            'gambar' => 'default.jpg',
            'penulis' => 'Tim Medis Poliklinik',
            'kategori' => 'Kesehatan Umum',
            'created_at' => now()->subDays(15)
        ]);

        Artikel::create([
            'judul' => 'Tips Menjaga Kesehatan di Tempat Kerja',
            'konten' => 'Bekerja di kantor seharian dapat mempengaruhi kesehatan. Berikut tips untuk menjaga kesehatan: 1) Istirahat teratur setiap 2 jam, 2) Konsumsi air putih yang cukup, 3) Jaga postur tubuh saat duduk, 4) Lakukan peregangan ringan.',
            'gambar' => 'default.jpg',
            'penulis' => 'dr. Budi Santoso',
            'kategori' => 'Kesehatan Kerja',
            'created_at' => now()->subDays(10)
        ]);

        Artikel::create([
            'judul' => 'Pentingnya Medical Check Up Rutin',
            'konten' => 'Medical check up rutin sangat penting untuk mendeteksi dini berbagai penyakit. Dengan pemeriksaan berkala, masalah kesehatan dapat diketahui sejak awal sehingga penanganan bisa lebih efektif.',
            'gambar' => 'default.jpg',
            'penulis' => 'dr. Aulia Putri',
            'kategori' => 'Pencegahan',
            'created_at' => now()->subDays(5)
        ]);

        Artikel::create([
            'judul' => 'Cara Mengelola Stres Kerja',
            'konten' => 'Stres kerja adalah hal yang umum terjadi. Untuk mengelolanya: 1) Identifikasi sumber stres, 2) Atur waktu dengan baik, 3) Lakukan hobi di luar pekerjaan, 4) Berbicara dengan rekan atau konselor jika diperlukan.',
            'gambar' => 'default.jpg',
            'penulis' => 'dr. Citra Dewi',
            'kategori' => 'Mental Health',
            'created_at' => now()->subDays(2)
        ]);

        Artikel::create([
            'judul' => 'Nutrisi Seimbang untuk Pekerja',
            'konten' => 'Nutrisi yang baik meningkatkan produktivitas kerja. Pastikan menu harian Anda mengandung karbohidrat kompleks, protein, sayuran, dan buah-buahan. Hindari makanan cepat saji yang berlebihan.',
            'gambar' => 'default.jpg',
            'penulis' => 'Tim Medis Poliklinik',
            'kategori' => 'Gizi',
            'created_at' => now()->subDays(1)
        ]);

        Artikel::create([
            'judul' => 'Manfaat Olahraga Ringan Setiap Hari',
            'konten' => 'Olahraga ringan seperti jalan kaki 30 menit sehari dapat meningkatkan kesehatan jantung, mengurangi risiko diabetes, dan meningkatkan mood. Tidak perlu olahraga berat, yang penting konsisten.',
            'gambar' => 'default.jpg',
            'penulis' => 'dr. Budi Santoso',
            'kategori' => 'Olahraga',
            'created_at' => now()
        ]);
    }
}
