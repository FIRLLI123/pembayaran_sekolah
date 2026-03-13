<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama_kelas' => 'X IPA 1', 'tahun_ajaran' => '2024/2025'],
            ['nama_kelas' => 'X IPA 2', 'tahun_ajaran' => '2024/2025'],
            ['nama_kelas' => 'XI IPS 1', 'tahun_ajaran' => '2024/2025'],
        ];

        foreach ($data as $item) {
            Kelas::create([
                'nama_kelas' => $item['nama_kelas'],
                'tahun_ajaran' => $item['tahun_ajaran'],
                'created_user' => 'Seeder',
            ]);
        }
    }
}
