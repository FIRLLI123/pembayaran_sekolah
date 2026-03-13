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
            'name' => 'Admin',
            'email' => 'admin@sekolah.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'created_user' => 'Seeder',
        ]);

        User::create([
            'name' => 'Petugas',
            'email' => 'petugas@sekolah.test',
            'password' => Hash::make('password'),
            'role' => 'petugas',
            'created_user' => 'Seeder',
        ]);
    }
}
