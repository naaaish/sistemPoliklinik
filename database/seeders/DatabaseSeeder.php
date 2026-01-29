<?php

namespace Database\Seeders;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            // 1) master paling dasar
            UserSeeder::class,

            // 2) master pegawai & keluarga (karena keluarga FK ke pegawai)
            PegawaiSeeder::class,
            KeluargaSeeder::class,

            // 3) master klinik
            DokterSeeder::class,
            PemeriksaSeeder::class,
            JadwalDokterSeeder::class,

            // 4) master medis
            DiagnosaSeeder::class,
            SaranSeeder::class,
            ObatSeeder::class,

            // 5) transaksi inti
            PendaftaranSeeder::class,
            PemeriksaanSeeder::class,

            // 6) detail pemeriksaan (relasi ke pemeriksaan)
            DetailPemeriksaanPenyakitSeeder::class,
            DetailPemeriksaanSaranSeeder::class,

            // 7) resep (relasi ke pemeriksaan dan obat)
            ResepSeeder::class,
            DetailResepSeeder::class,

            // 8) konten (bebas taruh akhir/awal, ga FK biasanya)
            ArtikelSeeder::class,
        ]);
    }
}

