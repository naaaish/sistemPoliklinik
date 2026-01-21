<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pendaftaran;
use App\Models\Pegawai;
use App\Models\Keluarga;
use App\Models\Dokter;    // Pastikan Model Dokter di-import
use App\Models\Pemeriksa; // Pastikan Model Pemeriksa di-import

class PendaftaranSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil Data Referensi (Pegawai, Dokter, Pemeriksa)
        $pegawai = Pegawai::where('nip', '198765432001')->first();
        
        // Ambil satu dokter dan satu pemeriksa secara acak/pertama
        $dokter = Dokter::first(); 
        $pemeriksa = Pemeriksa::first();

        if (!$pegawai) return;

        // ---------------------------------------------------------
        // KASUS 1: Pegawai Cek Kesehatan (Ditangani Pemeriksa)
        // ---------------------------------------------------------
        Pendaftaran::updateOrCreate(
            ['id_pendaftaran' => 'DFT001'],
            [
                'tanggal'           => now()->toDateString(),
                'jenis_pemeriksaan' => 'cek_kesehatan',
                'keluhan'           => 'Pusing dan lemas',
                'tipe_pasien'       => 'pegawai',
                'nip'               => $pegawai->nip,
                'id_keluarga'       => null,
                
                // Isi ID Pemeriksa, kosongkan ID Dokter
                'id_pemeriksa'      => $pemeriksa ? $pemeriksa->id_pemeriksa : null,
                'id_dokter'         => null, 
            ]
        );

        // ---------------------------------------------------------
        // KASUS 2: Anak Pegawai Berobat (Ditangani Dokter)
        // ---------------------------------------------------------
        $anak1 = Keluarga::where('nip', $pegawai->nip)
            ->where('hubungan_keluarga', 'anak')
            ->orderBy('urutan_anak')
            ->first();

        if ($anak1) {
            Pendaftaran::updateOrCreate(
                ['id_pendaftaran' => 'DFT002'],
                [
                    'tanggal'           => now()->toDateString(),
                    'jenis_pemeriksaan' => 'berobat',
                    'keluhan'           => 'Demam tinggi dan batuk',
                    'tipe_pasien'       => 'keluarga',
                    'nip'               => $pegawai->nip,
                    'id_keluarga'       => $anak1->id_keluarga,

                    // Isi ID Dokter, kosongkan ID Pemeriksa
                    'id_dokter'         => $dokter ? $dokter->id_dokter : null,
                    'id_pemeriksa'      => null,
                ]
            );
        }
    }
}