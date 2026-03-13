<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Siswa;
use App\Models\Kelas;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $kelas = Kelas::pluck('id')->toArray();

        $data = [
            ['nis' => '2024001', 'nama_siswa' => 'Ahmad Fauzi'],
            ['nis' => '2024002', 'nama_siswa' => 'Budi Santoso'],
            ['nis' => '2024003', 'nama_siswa' => 'Citra Lestari'],
            ['nis' => '2024004', 'nama_siswa' => 'Dewi Anggraini'],
            ['nis' => '2024005', 'nama_siswa' => 'Eko Prasetyo'],
        ];

        foreach ($data as $item) {
            Siswa::create([
                'nis' => $item['nis'],
                'nama_siswa' => $item['nama_siswa'],
                'kelas_id' => $kelas[array_rand($kelas)],
                'alamat' => 'Jl. Contoh No. ' . rand(1, 100),
                'no_hp' => '08' . rand(1111111111, 9999999999),
                'created_user' => 'Seeder',
            ]);
        }
    }
}
