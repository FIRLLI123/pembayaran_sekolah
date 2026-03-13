<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\JenisPembayaran;
use Carbon\Carbon;

class PembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $siswa = Siswa::all();
        $jenis = JenisPembayaran::all();

        foreach ($siswa as $s) {
            // tiap siswa 2–3 pembayaran
            $total = rand(2, 3);

            for ($i = 1; $i <= $total; $i++) {
                $jp = $jenis->random();

                Pembayaran::create([
                    'siswa_id' => $s->id,
                    'jenis_pembayaran_id' => $jp->id,
                    'tanggal_bayar' => Carbon::now()->subDays(rand(1, 30)),
                    'nominal_bayar' => $jp->nominal_default,
                    'metode_bayar' => rand(0, 1) ? 'cash' : 'transfer',
                    'status' => 'lunas',
                    'keterangan' => 'Pembayaran dummy seeder',
                    'created_user' => 'Seeder',
                ]);
            }
        }
    }
}
