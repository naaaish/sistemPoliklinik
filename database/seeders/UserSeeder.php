<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username'  => 'poliklinik',
            'password'  => Hash::make('poli123'),
            'role'      => 'adminPoli',
            'nama_user' => 'Admin Poliklinik',
            'nip'       => null,
        ]);

        User::create([
            'username'  => 'kepegawaian',
            'password'  => Hash::make('kepeg123'),
            'role'      => 'adminKepegawaian',
            'nama_user' => 'Admin Kepegawaian',
            'nip'       => null,
        ]);

        User::create([
            'username'  => '198765432001',
            'password'  => Hash::make('pasien123'),
            'role'      => 'pasien',
            'nama_user' => 'Dr. Ahmad Pratama',
            'nip'       => '198765432001', 
        ]);

        User::create([
            'username'  => '198765432002',
            'password'  => Hash::make('pasien123'),
            'role'      => 'pasien',
            'nama_user' => 'Siti Aisyah',
            'nip'       => '198765432002', 
        ]);

        User::create([
            'username'  => '198765432003',
            'password'  => Hash::make('budi123'),
            'role'      => 'pasien',
            'nama_user' => 'Budi Santoso',
            'nip'       => '198765432003', 
        ]);
    }
}