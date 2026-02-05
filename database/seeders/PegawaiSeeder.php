<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        // DB::table('pegawai')->insert([
        //     [
        //         'nip' => '198765432001',
        //         'nama_pegawai' => 'Ahmad Pratama',
        //         'jenis_kelamin' => 'Laki-laki',
        //         'tgl_lahir' => '1990-01-01',
        //         'no_telp' => '081234567890',
        //         'email' => 'ahmad.pratama@poliklinik.test',
        //         'alamat' => 'Jl. Merdeka No. 10, Jakarta',
        //         'jabatan' => 'Staf',
        //         'bagian' => 'SDM',
        //         'is_active' => 1,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'nip' => '198765432002',
        //         'nama_pegawai' => 'Siti Aisyah',
        //         'jenis_kelamin' => 'Perempuan',
        //         'tgl_lahir' => '1992-02-02',
        //         'no_telp' => '082345678901',
        //         'email' => 'siti.aisyah@poliklinik.test',
        //         'alamat' => 'Jl. Sudirman No. 5, Bandung',
        //         'jabatan' => 'Asisten Manajer',
        //         'bagian' => 'Keuangan',
        //         'is_active' => 1,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     // untuk poliklinik
        //     [
        //         'nip' => '001',
        //         'nama_pegawai' => 'Poliklinik',
        //         'jenis_kelamin' => '-',
        //         'tgl_lahir' => Carbon::now()->toDateString(),
        //         'no_telp' => '-',
        //         'email' => '-',
        //         'alamat' => '-',
        //         'jabatan' => '-',
        //         'bagian' => '-',
        //         'is_active' => 1,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         // data pensiunan
        //         'nip' => '198765432003',
        //         'nama_pegawai' => 'Budi Santoso',
        //         'jenis_kelamin' => 'Laki-laki',
        //         'tgl_lahir' => '1985-03-03',
        //         'no_telp' => '083456789012',
        //         'email' => 'budi.santoso@poliklinik.test',
        //         'alamat' => 'Jl. Gatot Subroto No. 8, Surabaya',
        //         'jabatan' => '-',
        //         'bagian' => 'Pensiunan',
        //         'is_active' => 1,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'nip' => '198765432004',
        //         'nama_pegawai' => 'Linda Marlina',
        //         'jenis_kelamin' => 'Perempuan',
        //         'tgl_lahir' => '1994-04-04',
        //         'no_telp' => '084567890123',
        //         'email' => 'linda.marlina@poliklinik.test',
        //         'alamat' => 'Jl. Ahmad Yani No. 12, Medan',
        //         'jabatan' => 'Staf',
        //         'bagian' => 'Administrasi',
        //         'is_active' => 1,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'nip' => '198765432005',
        //         'nama_pegawai' => 'Rina Kusuma',
        //         'jenis_kelamin' => 'Perempuan',
        //         'tgl_lahir' => '1995-05-05',
        //         'no_telp' => '085678901234',
        //         'email' => 'rina.kusuma@poliklinik.test',
        //         'alamat' => 'Jl. Diponegoro No. 20, Yogyakarta',
        //         'jabatan' => 'Staf',
        //         'bagian' => 'Pelayanan',
        //         'is_active' => 1,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         // untuk bagian = OJT
        //         'nip' => '198765432006',
        //         'nama_pegawai' => 'Andi Wijaya',
        //         'jenis_kelamin' => 'Laki-laki',
        //         'tgl_lahir' => '1996-06-06',
        //         'no_telp' => '086789012345',
        //         'email' => 'andiwijaya@polilinik.test',
        //         'alamat' => 'Jl. Pahlawan No. 15, Semarang',
        //         'jabatan' => 'Staff',
        //         'bagian' => 'OJT',
        //         'is_active' => 1,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         // untuk bagian = Unit Lain
        //         'nip' => '198765432007',
        //         'nama_pegawai' => 'Sari Putri',
        //         'jenis_kelamin' => 'Perempuan',
        //         'tgl_lahir' => '1997-07-07',
        //         'no_telp' => '087890123456',
        //         'email' => 'sari123@poliklinik.test',
        //         'alamat' => 'Jl. Kenangan No. 22, Bali',
        //         'jabatan' => 'Staff',
        //         'bagian' => 'Unit Lain',
        //         'is_active' => 1,
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ]

        // ]);
    }
}
