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
                'nama_pegawai' => 'Ahmad Pratama',
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
                'bagian' => 'SDM',
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
                'bagian' => 'Keuangan',
                'foto' => null,
                'pendidikan_terakhir' => 'S1 Akuntansi',
                'institusi' => 'Universitas Padjadjaran',
                'thn_lulus' => '2016',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            [
                'nip' => '1234567890',
                'nama_pegawai' => 'Elvina Neila',
                'nik' => '3275010101900005',
                'agama' => 'Islam',
                'jenis_kelamin' => 'Perempuan',
                'tgl_lahir' => '2005-02-09',
                'tgl_masuk' => '2027-06-15',
                'status' => 'Aktif',
                'status_pernikahan' => 'Belum Menikah',
                'no_telp' => '081234567899',
                'email' => 'elvina.neila@poliklinik.test',
                'alamat' => 'Jl. Merdeka No. 10, Semarang',
                'jabatan' => 'Staf',
                'bagian' => 'SIS',
                'foto' => null,
                'pendidikan_terakhir' => 'S1 Informatika',
                'institusi' => 'Universitas Diponegoro',
                'thn_lulus' => '2027',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // untuk poliklinik
            [
                'nip' => '001',
                'nama_pegawai' => 'Poliklinik',
                'nik' => '-',
                'agama' => '-',
                'jenis_kelamin' => '-',
                'tgl_lahir' => '2000-01-01',
                'tgl_masuk' => '2000-01-01',
                'status' => 'Aktif',
                'status_pernikahan' => '-',
                'no_telp' => '-',
                'email' => '-',
                'alamat' => '-',
                'jabatan' => '-',
                'bagian' => 'Poliklinik',
                'foto' => null,
                'pendidikan_terakhir' => '-',
                'institusi' => '-',
                'thn_lulus' => '-',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
