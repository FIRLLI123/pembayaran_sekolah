<?php

namespace App\Services;

use App\Models\JenisPembayaran;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TagihanService
{
    public function generateSpp(array $payload): array
    {
        $tahun = (int) $payload['tahun'];
        $bulanList = $payload['bulan'];
        $kelasId = !empty($payload['kelas_id']) ? (int) $payload['kelas_id'] : null;
        $siswaId = !empty($payload['siswa_id']) ? (int) $payload['siswa_id'] : null;
        $nominalCustom = isset($payload['nominal_custom']) && $payload['nominal_custom'] !== null
            ? (int) $payload['nominal_custom']
            : null;

        $jenisSPP = JenisPembayaran::where('tipe', 'rutin')
            ->where('periode', 'bulanan')
            ->get();

        if ($jenisSPP->isEmpty()) {
            throw new \RuntimeException('Jenis pembayaran SPP rutin bulanan belum tersedia.');
        }

        $siswaQuery = Siswa::query();
        if ($kelasId) {
            $siswaQuery->where('kelas_id', $kelasId);
        }
        if ($siswaId) {
            $siswaQuery->where('id', $siswaId);
        }

        $siswaList = $siswaQuery->get();
        if ($siswaList->isEmpty()) {
            throw new \RuntimeException('Tidak ada siswa sesuai filter kelas/siswa yang dipilih.');
        }

        $berhasil = [];
        $sudahAda = [];

        foreach ($bulanList as $bulan) {
            $semuaSudahAda = true;

            foreach ($siswaList as $siswa) {
                foreach ($jenisSPP as $jenis) {
                    $exists = Tagihan::where('siswa_id', $siswa->id)
                        ->where('jenis_pembayaran_id', $jenis->id)
                        ->where('periode_bulan', $bulan)
                        ->where('periode_tahun', $tahun)
                        ->exists();

                    if (!$exists) {
                        $semuaSudahAda = false;

                        Tagihan::create([
                            'siswa_id' => $siswa->id,
                            'jenis_pembayaran_id' => $jenis->id,
                            'tanggal_tagihan' => now(),
                            'jatuh_tempo' => now()->addDays(10),
                            'periode_bulan' => $bulan,
                            'periode_tahun' => $tahun,
                            'nominal_tagihan' => $nominalCustom !== null ? $nominalCustom : $jenis->nominal_default,
                            'sisa_tagihan' => $nominalCustom !== null ? $nominalCustom : $jenis->nominal_default,
                            'status' => 'belum_bayar',
                            'created_user' => Auth::user()->name ?? 'system',
                        ]);
                    }
                }
            }

            $namaBulan = Carbon::create()->month($bulan)->translatedFormat('F');
            if ($semuaSudahAda) {
                $sudahAda[] = $namaBulan;
            } else {
                $berhasil[] = $namaBulan;
            }
        }

        return [
            'berhasil' => $berhasil,
            'sudah_ada' => $sudahAda,
            'tahun' => $tahun,
        ];
    }

    public function generateCustom(array $payload): array
    {
        $jenis = JenisPembayaran::findOrFail($payload['jenis_pembayaran_id']);
        $bulan = (int) now()->month;
        $tahun = (int) now()->year;
        $force = !empty($payload['force']);
        $siswaIds = $payload['siswa_id'];

        $siswaList = Siswa::whereIn('id', $siswaIds)->pluck('nama_siswa', 'id');
        $sudahAda = [];

        foreach ($siswaIds as $siswaId) {
            $exists = Tagihan::where('siswa_id', $siswaId)
                ->where('jenis_pembayaran_id', $jenis->id)
                ->where('periode_bulan', $bulan)
                ->where('periode_tahun', $tahun)
                ->exists();

            if ($exists) {
                $sudahAda[] = $siswaList[$siswaId] ?? 'Unknown';
            }
        }

        if (!empty($sudahAda) && !$force) {
            return [
                'requires_confirmation' => true,
                'message' => 'Beberapa siswa sudah memiliki tagihan ini di bulan ini.',
                'duplicates' => array_values($sudahAda),
            ];
        }

        DB::transaction(function () use ($payload, $jenis, $siswaIds, $bulan, $tahun) {
            foreach ($siswaIds as $siswaId) {
                Tagihan::create([
                    'siswa_id' => $siswaId,
                    'jenis_pembayaran_id' => $jenis->id,
                    'tanggal_tagihan' => now(),
                    'jatuh_tempo' => !empty($payload['jatuh_tempo']) ? $payload['jatuh_tempo'] : now()->addDays(7),
                    'periode_bulan' => $bulan,
                    'periode_tahun' => $tahun,
                    'nominal_tagihan' => !empty($payload['nominal_custom']) ? $payload['nominal_custom'] : $jenis->nominal_default,
                    'sisa_tagihan' => !empty($payload['nominal_custom']) ? $payload['nominal_custom'] : $jenis->nominal_default,
                    'status' => 'belum_bayar',
                    'created_user' => auth()->user()->name ?? 'admin',
                ]);
            }
        });

        return [
            'requires_confirmation' => false,
            'message' => 'Tagihan custom berhasil dibuat.',
        ];
    }

    public function deleteGenerated(array $payload): int
    {
        $tahun = (int) $payload['tahun'];
        $bulanList = $payload['bulan'];

        $targetQuery = Tagihan::query()
            ->where('periode_tahun', $tahun)
            ->whereIn('periode_bulan', $bulanList);

        if (!empty($payload['kelas_id'])) {
            $kelasId = (int) $payload['kelas_id'];
            $targetQuery->whereHas('siswa', function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }

        if (!empty($payload['siswa_id'])) {
            $targetQuery->where('siswa_id', $payload['siswa_id']);
        }

        if (!empty($payload['jenis_pembayaran_id'])) {
            $targetQuery->where('jenis_pembayaran_id', $payload['jenis_pembayaran_id']);
        }

        $targetTagihanIds = (clone $targetQuery)->pluck('id');
        if ($targetTagihanIds->isEmpty()) {
            throw new \RuntimeException('Tidak ada data tagihan generated yang cocok dengan filter.');
        }

        $tagihanSudahDibayarCount = Pembayaran::whereIn('tagihan_id', $targetTagihanIds)
            ->distinct('tagihan_id')
            ->count('tagihan_id');

        if ($tagihanSudahDibayarCount > 0) {
            throw new \RuntimeException(
                'Hapus dibatalkan. Ada ' . $tagihanSudahDibayarCount . ' tagihan pada filter ini yang sudah masuk pembayaran.'
            );
        }

        return DB::transaction(function () use ($targetTagihanIds) {
            return Tagihan::whereIn('id', $targetTagihanIds)->delete();
        });
    }

    public function statusBulan($tahun): array
    {
        $result = [];

        for ($i = 1; $i <= 12; $i++) {
            $jumlahSiswa = Siswa::count();
            $jumlahTagihan = Tagihan::where('periode_bulan', $i)
                ->where('periode_tahun', $tahun)
                ->distinct('siswa_id')
                ->count('siswa_id');

            $result[$i] = $jumlahTagihan >= $jumlahSiswa && $jumlahSiswa > 0;
        }

        return $result;
    }
}
