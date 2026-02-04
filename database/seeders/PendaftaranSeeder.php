<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pendaftaran;
use App\Models\Pegawai;
use App\Models\Keluarga;
use App\Models\Dokter;
use App\Models\Pemeriksa;

class PendaftaranSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil Data Pendukung
        $pegawai   = Pegawai::where('nip', '198765432001')->first();
        $dokter    = Dokter::where('id_dokter', 'DOK001')->first(); // dr. Farah
        $pemeriksa = Pemeriksa::where('id_pemeriksa', 'PMR001')->first(); // Sofia

        if (!$pegawai) return;

        // KASUS 1: Pegawai Sakit Kepala (Cek Kesehatan)
        Pendaftaran::updateOrCreate(
            ['id_pendaftaran' => 'REG-001'],
            [
                'tanggal'           => now()->toDateString(),
                'jenis_pemeriksaan' => 'cek_kesehatan',
                'keluhan'           => 'Pusing berat, tengkuk kaku',
                'tipe_pasien'       => 'pegawai',
                'nip'               => $pegawai->nip,
                'id_keluarga'       => null,
                'id_pemeriksa'      => $pemeriksa ? $pemeriksa->id_pemeriksa : null,
                'id_dokter'         => null, 
            ]
        );

        // KASUS 2: Anak Pegawai Demam (Berobat)
        $anak1 = Keluarga::where('nip', $pegawai->nip)
            ->where('hubungan_keluarga', 'anak')
            ->orderBy('urutan_anak')
            ->first(); // Budi Pratama

        if ($anak1) {
            Pendaftaran::updateOrCreate(
                ['id_pendaftaran' => 'REG-002'],
                [
                    'tanggal'           => now()->toDateString(),
                    'jenis_pemeriksaan' => 'periksa',
                    'keluhan'           => 'Demam tinggi, batuk pilek',
                    'tipe_pasien'       => 'keluarga',
                    'nip'               => $pegawai->nip,
                    'id_keluarga'       => $anak1->id_keluarga,
                    'id_dokter'         => $dokter ? $dokter->id_dokter : null,
                    'id_pemeriksa'      => null,
                ]
            );
        }
    }
}