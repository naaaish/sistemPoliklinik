<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pendaftaran;
use App\Models\Pegawai;
use App\Models\Keluarga;

class PendaftaranSeeder extends Seeder
{
    public function run(): void
    {
        $pegawai = Pegawai::where('nip', '198765432001')->first();
        if (!$pegawai) return;

        // pendaftaran pegawai
        Pendaftaran::updateOrCreate(
            ['id_pendaftaran' => 'DFT001'],
            [
                'tanggal' => now()->toDateString(),
                'jenis_pemeriksaan' => 'cek_kesehatan',
                'keluhan' => 'Pusing',
                'tipe_pasien' => 'pegawai',
                'nip' => $pegawai->nip,
                'id_keluarga' => null,
            ]
        );

        // pendaftaran keluarga: anak pertama
        $anak1 = Keluarga::where('nip', $pegawai->nip)
            ->where('hubungan_keluarga', 'anak')
            ->orderBy('urutan_anak')
            ->first();

        if ($anak1) {
            Pendaftaran::updateOrCreate(
                ['id_pendaftaran' => 'DFT002'],
                [
                    'tanggal' => now()->toDateString(),
                    'jenis_pemeriksaan' => 'berobat',
                    'keluhan' => 'Batuk',
                    'tipe_pasien' => 'keluarga',
                    'nip' => $pegawai->nip,
                    'id_keluarga' => $anak1->id_keluarga,
                ]
            );
        }
    }
}
