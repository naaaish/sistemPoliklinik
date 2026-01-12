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
        ]);

        User::create([
            'username'  => 'kepegawaian',
            'password'  => Hash::make('kepeg123'),
            'role'      => 'adminKepegawaian',
            'nama_user' => 'Admin Kepegawaian',
        ]);

        User::create([
            'username'  => 'pasien',
            'password'  => Hash::make('pasien123'),
            'role'      => 'pasien',
            'nama_user' => 'Pasien 1',
        ]);
    }
}
