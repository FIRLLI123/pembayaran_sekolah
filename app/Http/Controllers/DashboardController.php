<?php

namespace App\Http\Controllers;

use App\Models\JenisPembayaran;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role ?? null;

        if ($role === 'ortu') {
            $siswa = null;

            if ($user && $user->siswa_id) {
                $siswa = Siswa::with('kelas')->find($user->siswa_id);
            }

            if (
                !$siswa &&
                $user &&
                $user->ortu &&
                Schema::hasColumn('siswa', 'ortu_id')
            ) {
                $siswa = Siswa::with('kelas')
                    ->where('ortu_id', $user->ortu->id)
                    ->first();
            }

            $now = Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth()->toDateString();
            $endOfMonth = $now->copy()->endOfMonth()->toDateString();

            $totalTagihanAktifBulanIni = 0;
            $sisaTagihanBelumDibayar = 0;
            $jumlahTagihanMenunggu = 0;
            $riwayatTagihan = null;

            if ($siswa) {
                $totalTagihanAktifBulanIni = (int) Tagihan::query()
                    ->where('siswa_id', $siswa->id)
                    ->whereBetween('tanggal_tagihan', [$startOfMonth, $endOfMonth])
                    ->sum('nominal_tagihan');

                $sisaTagihanBelumDibayar = (int) Tagihan::query()
                    ->where('siswa_id', $siswa->id)
                    ->whereIn('status', ['belum_bayar', 'cicil'])
                    ->sum('sisa_tagihan');

                $jumlahTagihanMenunggu = (int) Tagihan::query()
                    ->where('siswa_id', $siswa->id)
                    ->whereIn('status', ['belum_bayar', 'cicil'])
                    ->count();

                $riwayatTagihan = Tagihan::with('jenisPembayaran')
                    ->where('siswa_id', $siswa->id)
                    ->orderByDesc('tanggal_tagihan')
                    ->orderByDesc('id')
                    ->paginate(5, ['*'], 'tagihan_page')
                    ->withQueryString();
            }

            $statusRingkasan = $jumlahTagihanMenunggu > 0
                ? $jumlahTagihanMenunggu . ' tagihan menunggu pembayaran'
                : 'Semua tagihan sudah lunas';

            $ortu = $user ? $user->ortu : null;
            $namaWali = collect([
                optional($ortu)->nama_ayah,
                optional($ortu)->nama_ibu,
            ])->filter()->implode(' / ');

            if ($namaWali === '') {
                $namaWali = $user->name ?? '-';
            }

            return view('dashboard2', [
                'user' => $user,
                'siswa' => $siswa,
                'ortu' => $ortu,
                'namaWali' => $namaWali,
                'totalTagihanAktifBulanIni' => $totalTagihanAktifBulanIni,
                'sisaTagihanBelumDibayar' => $sisaTagihanBelumDibayar,
                'jumlahTagihanMenunggu' => $jumlahTagihanMenunggu,
                'statusRingkasan' => $statusRingkasan,
                'riwayatTagihan' => $riwayatTagihan,
            ]);
        }

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        $totalTagihanBulanIni = (int) Tagihan::query()
            ->whereBetween('tanggal_tagihan', [$startOfMonth, $endOfMonth])
            ->sum('nominal_tagihan');

        $totalPembayaranMasuk = (int) Pembayaran::query()
            ->whereIn('status', ['lunas', 'cicil'])
            ->whereBetween('tanggal_bayar', [$startOfMonth, $endOfMonth])
            ->sum('nominal_bayar');

        $jumlahSiswaBelumLunas = (int) Tagihan::query()
            ->whereIn('status', ['belum_bayar', 'cicil'])
            ->distinct('siswa_id')
            ->count('siswa_id');

        $jumlahSiswaAktif = (int) Siswa::count();

        $monthsToShow = 12;
        $startMonth = $now->copy()->startOfMonth()->subMonths($monthsToShow - 1);

        $tagihanBulananRaw = Tagihan::query()
            ->selectRaw('YEAR(tanggal_tagihan) as tahun, MONTH(tanggal_tagihan) as bulan, SUM(nominal_tagihan) as total')
            ->whereDate('tanggal_tagihan', '>=', $startMonth->toDateString())
            ->groupByRaw('YEAR(tanggal_tagihan), MONTH(tanggal_tagihan)')
            ->get();

        $pembayaranBulananRaw = Pembayaran::query()
            ->selectRaw('YEAR(tanggal_bayar) as tahun, MONTH(tanggal_bayar) as bulan, SUM(nominal_bayar) as total')
            ->whereIn('status', ['lunas', 'cicil'])
            ->whereDate('tanggal_bayar', '>=', $startMonth->toDateString())
            ->groupByRaw('YEAR(tanggal_bayar), MONTH(tanggal_bayar)')
            ->get();

        $tagihanMap = [];
        foreach ($tagihanBulananRaw as $item) {
            $key = sprintf('%04d-%02d', (int) $item->tahun, (int) $item->bulan);
            $tagihanMap[$key] = (int) $item->total;
        }

        $pembayaranMap = [];
        foreach ($pembayaranBulananRaw as $item) {
            $key = sprintf('%04d-%02d', (int) $item->tahun, (int) $item->bulan);
            $pembayaranMap[$key] = (int) $item->total;
        }

        $chartLabels = [];
        $chartTargetTagihan = [];
        $chartRealisasiPembayaran = [];

        for ($i = 0; $i < $monthsToShow; $i++) {
            $monthCursor = $startMonth->copy()->addMonths($i);
            $key = $monthCursor->format('Y-m');

            $chartLabels[] = $monthCursor->translatedFormat('M Y');
            $chartTargetTagihan[] = $tagihanMap[$key] ?? 0;
            $chartRealisasiPembayaran[] = $pembayaranMap[$key] ?? 0;
        }

        $statusTagihan = [
            'lunas' => (int) Tagihan::query()->where('status', 'lunas')->count(),
            'cicil' => (int) Tagihan::query()->where('status', 'cicil')->count(),
            'belum_bayar' => (int) Tagihan::query()->where('status', 'belum_bayar')->count(),
        ];

        $totalStatusTagihan = array_sum($statusTagihan);

        $tagihanPerJenis = DB::table('tagihan')
            ->select('jenis_pembayaran_id', DB::raw('SUM(nominal_tagihan) as total_tagihan'))
            ->groupBy('jenis_pembayaran_id');

        $pembayaranPerJenis = DB::table('pembayaran')
            ->select('jenis_pembayaran_id', DB::raw('SUM(nominal_bayar) as total_bayar'))
            ->whereIn('status', ['lunas', 'cicil'])
            ->groupBy('jenis_pembayaran_id');

        $progressPerJenis = JenisPembayaran::query()
            ->leftJoinSub($tagihanPerJenis, 't', function ($join) {
                $join->on('jenis_pembayaran.id', '=', 't.jenis_pembayaran_id');
            })
            ->leftJoinSub($pembayaranPerJenis, 'p', function ($join) {
                $join->on('jenis_pembayaran.id', '=', 'p.jenis_pembayaran_id');
            })
            ->orderBy('jenis_pembayaran.nama_pembayaran')
            ->get([
                'jenis_pembayaran.id',
                'jenis_pembayaran.nama_pembayaran',
                DB::raw('COALESCE(t.total_tagihan, 0) as total_tagihan'),
                DB::raw('COALESCE(p.total_bayar, 0) as total_bayar'),
            ])
            ->map(function ($item) {
                $totalTagihan = (int) $item->total_tagihan;
                $totalBayar = (int) $item->total_bayar;
                $persentase = $totalTagihan > 0
                    ? min(100, round(($totalBayar / $totalTagihan) * 100, 2))
                    : 0;

                return [
                    'id' => (int) $item->id,
                    'nama_pembayaran' => $item->nama_pembayaran,
                    'total_tagihan' => $totalTagihan,
                    'total_bayar' => $totalBayar,
                    'persentase' => $persentase,
                ];
            });

        return view('dashboard', [
            'totalTagihanBulanIni' => $totalTagihanBulanIni,
            'totalPembayaranMasuk' => $totalPembayaranMasuk,
            'jumlahSiswaBelumLunas' => $jumlahSiswaBelumLunas,
            'jumlahSiswaAktif' => $jumlahSiswaAktif,
            'chartLabels' => $chartLabels,
            'chartTargetTagihan' => $chartTargetTagihan,
            'chartRealisasiPembayaran' => $chartRealisasiPembayaran,
            'statusTagihan' => $statusTagihan,
            'totalStatusTagihan' => $totalStatusTagihan,
            'progressPerJenis' => $progressPerJenis,
        ]);
    }
}
