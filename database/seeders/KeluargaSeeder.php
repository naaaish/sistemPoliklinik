<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Keluarga;
use App\Models\Pegawai;

class KeluargaSeeder extends Seeder
{
    public function run(): void
    {
        $pegawai = Pegawai::where('nip', '198765432001')->first();
        if (!$pegawai) return;

        // Pasangan (maks 1)
        Keluarga::updateOrCreate(
            ['id_keluarga' => $pegawai->nip . 'I1'],
            [
                'nip' => $pegawai->nip,
                'hubungan_keluarga' => 'pasangan',
                'urutan_anak' => null,
                'nama_keluarga' => 'Siti Aminah',
                'tgl_lahir' => '1987-05-10',
                'jenis_kelamin' => 'P',
            ]
        );

        // 5 anak (tetap masuk DB)
        for ($i = 1; $i <= 5; $i++) {
            Keluarga::updateOrCreate(
                ['id_keluarga' => $pegawai->nip . 'A' . $i],
                [
                    'nip' => $pegawai->nip,
                    'hubungan_keluarga' => 'anak',
                    'urutan_anak' => $i,
                    'nama_keluarga' => "Anak ke-$i",
                    'tgl_lahir' => now()->subYears(5 + $i)->toDateString(),
                    'jenis_kelamin' => $i % 2 ? 'L' : 'P',
                ]
            );
        }
    }
}
