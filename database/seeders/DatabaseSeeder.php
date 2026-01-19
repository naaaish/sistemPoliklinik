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
            UserSeeder::class,
            PegawaiSeeder::class,
            DokterSeeder::class,
            ObatSeeder::class,
            DiagnosaK3Seeder::class,
            JadwalDokterSeeder::class,
            PemeriksaSeeder::class,
            SaranSeeder::class,
            DiagnosaSeeder::class,
            DiagnosaK3Seeder::class,
            PasienSeeder::class,
            PendaftaranSeeder::class,
            PemeriksaanSeeder::class,
            ResepSeeder::class,
            detailResepSeeder::class,
            ArtikelSeeder::class,
            DetailPemeriksaanPenyakitSeeder::class,
            DetailPemeriksaanDiagnosaK3Seeder::class,
            DetailPemeriksaanSaranSeeder::class,
        ]);
    }
}
