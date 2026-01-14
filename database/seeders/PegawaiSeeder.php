<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pegawai')->insert([
            [
                'nip' => '198765432001',
                'nama_pegawai' => 'Dr. Ahmad Pratama',
                'nik' => '3275010101900001',
                'agama' => 'Islam',
                'jenis_kelamin' => 'Laki-laki',
                'tgl_lahir' => '1990-01-01',
                'tgl_masuk' => '2020-01-15',
                'status' => 'Aktif',
                'status_pernikahan' => 'Menikah',
                'no_telp' => '081234567890',
                'email' => 'ahmad.pratama@poliklinik.test',
                'alamat' => 'Jl. Merdeka No. 10, Jakarta',
                'jabatan' => 'Staf',
                'bidang' => 'SDM',
                'foto' => null,
                'pendidikan_terakhir' => 'S1 Manajemen',
                'institusi' => 'Universitas Indonesia',
                'thn_lulus' => '2014',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nip' => '198765432002',
                'nama_pegawai' => 'Siti Aisyah',
                'nik' => '3275010202900002',
                'agama' => 'Islam',
                'jenis_kelamin' => 'Perempuan',
                'tgl_lahir' => '1992-02-02',
                'tgl_masuk' => '2021-03-10',
                'status' => 'Aktif',
                'status_pernikahan' => 'Belum Menikah',
                'no_telp' => '082345678901',
                'email' => 'siti.aisyah@poliklinik.test',
                'alamat' => 'Jl. Sudirman No. 5, Bandung',
                'jabatan' => 'Asisten Manajer',
                'bidang' => 'Keuangan',
                'foto' => null,
                'pendidikan_terakhir' => 'S1 Akuntansi',
                'institusi' => 'Universitas Padjadjaran',
                'thn_lulus' => '2016',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
