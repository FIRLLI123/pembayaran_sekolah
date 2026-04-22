<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ortu;

class OrtuSeeder extends Seeder
{
    public function run(): void
    {
        Ortu::insert([
            [
                'nama_ayah' => 'Budi Santoso',
                'nama_ibu' => 'Siti Aminah',
                'no_hp' => '081234567890',
                'alamat' => 'Jl. Mawar No. 1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_ayah' => 'Ahmad Hidayat',
                'nama_ibu' => 'Dewi Lestari',
                'no_hp' => '081298765432',
                'alamat' => 'Jl. Melati No. 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_ayah' => 'Rudi Hartono',
                'nama_ibu' => 'Sri Wahyuni',
                'no_hp' => '082112223333',
                'alamat' => 'Jl. Kenanga No. 3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}