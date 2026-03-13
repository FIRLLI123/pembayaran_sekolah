<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisPembayaran;

class JenisPembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama_pembayaran' => 'SPP',
                'nominal_default' => 250000,
                'keterangan' => 'Pembayaran SPP Bulanan'
            ],
            [
                'nama_pembayaran' => 'Daftar Ulang',
                'nominal_default' => 500000,
                'keterangan' => 'Biaya daftar ulang tahunan'
            ],
            [
                'nama_pembayaran' => 'Uang Gedung',
                'nominal_default' => 1500000,
                'keterangan' => 'Pembayaran uang gedung'
            ],
        ];

        foreach ($data as $item) {
            JenisPembayaran::create([
                'nama_pembayaran' => $item['nama_pembayaran'],
                'nominal_default' => $item['nominal_default'],
                'keterangan' => $item['keterangan'],
                'created_user' => 'Seeder',
            ]);
        }
    }
}
