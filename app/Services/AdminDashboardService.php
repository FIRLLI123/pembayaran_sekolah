<?php

namespace App\Services;

use App\Models\JenisPembayaran;
use App\Models\Kelas;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    public function getData(Request $request): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        $selectedSiswaId = $request->get('siswa_id');
        $selectedKelasId = $request->get('kelas_id');
        $selectedStatus = $request->get('status');
        $selectedPreset = $request->get('preset_periode');
        $periodeMulai = $request->get('periode_mulai');
        $periodeSelesai = $request->get('periode_selesai');

        if (in_array($selectedPreset, ['bulan_ini', '3_bulan', '1_tahun'], true)) {
            if ($selectedPreset === 'bulan_ini') {
                $periodeMulai = $now->copy()->startOfMonth()->toDateString();
                $periodeSelesai = $now->copy()->endOfMonth()->toDateString();
            } elseif ($selectedPreset === '3_bulan') {
                $periodeMulai = $now->copy()->subMonths(2)->startOfMonth()->toDateString();
                $periodeSelesai = $now->copy()->endOfMonth()->toDateString();
            } elseif ($selectedPreset === '1_tahun') {
                $periodeMulai = $now->copy()->subMonths(11)->startOfMonth()->toDateString();
                $periodeSelesai = $now->copy()->endOfMonth()->toDateString();
            }
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $periodeMulai)) {
            $periodeMulai = null;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $periodeSelesai)) {
            $periodeSelesai = null;
        }

        if ($periodeMulai && $periodeSelesai && $periodeMulai > $periodeSelesai) {
            $temp = $periodeMulai;
            $periodeMulai = $periodeSelesai;
            $periodeSelesai = $temp;
        }

        $applyTagihanFilter = function ($query, $useDefaultMonth) use (
            $selectedSiswaId,
            $selectedKelasId,
            $selectedStatus,
            $periodeMulai,
            $periodeSelesai,
            $startOfMonth,
            $endOfMonth
        ) {
            if ($selectedSiswaId) {
                $query->where('siswa_id', $selectedSiswaId);
            }

            if ($selectedKelasId) {
                $query->whereHas('siswa', function ($q) use ($selectedKelasId) {
                    $q->where('kelas_id', $selectedKelasId);
                });
            }

            if ($selectedStatus) {
                $query->where('status', $selectedStatus);
            }

            if ($periodeMulai && $periodeSelesai) {
                $query->whereBetween('tanggal_tagihan', [$periodeMulai, $periodeSelesai]);
            } elseif ($periodeMulai) {
                $query->whereDate('tanggal_tagihan', '>=', $periodeMulai);
            } elseif ($periodeSelesai) {
                $query->whereDate('tanggal_tagihan', '<=', $periodeSelesai);
            } elseif ($useDefaultMonth) {
                $query->whereBetween('tanggal_tagihan', [$startOfMonth, $endOfMonth]);
            }

            return $query;
        };

        $applyPembayaranFilter = function ($query, $useDefaultMonth) use (
            $selectedSiswaId,
            $selectedKelasId,
            $selectedStatus,
            $periodeMulai,
            $periodeSelesai,
            $startOfMonth,
            $endOfMonth
        ) {
            $query->whereIn('status', ['lunas', 'cicil']);

            if ($selectedSiswaId) {
                $query->where('siswa_id', $selectedSiswaId);
            }

            if ($selectedKelasId) {
                $query->whereHas('siswa', function ($q) use ($selectedKelasId) {
                    $q->where('kelas_id', $selectedKelasId);
                });
            }

            if ($selectedStatus) {
                $query->whereHas('tagihan', function ($q) use ($selectedStatus) {
                    $q->where('status', $selectedStatus);
                });
            }

            if ($periodeMulai && $periodeSelesai) {
                $query->whereBetween('tanggal_bayar', [$periodeMulai, $periodeSelesai]);
            } elseif ($periodeMulai) {
                $query->whereDate('tanggal_bayar', '>=', $periodeMulai);
            } elseif ($periodeSelesai) {
                $query->whereDate('tanggal_bayar', '<=', $periodeSelesai);
            } elseif ($useDefaultMonth) {
                $query->whereBetween('tanggal_bayar', [$startOfMonth, $endOfMonth]);
            }

            return $query;
        };

        $totalTagihanBulanIni = (int) $applyTagihanFilter(Tagihan::query(), true)->sum('nominal_tagihan');
        $totalPembayaranMasuk = (int) $applyPembayaranFilter(Pembayaran::query(), true)->sum('nominal_bayar');

        $jumlahSiswaBelumLunas = (int) $applyTagihanFilter(
            Tagihan::query()->whereIn('status', ['belum_bayar', 'cicil']),
            false
        )->distinct('siswa_id')->count('siswa_id');

        $jumlahSiswaAktifQuery = Siswa::query();
        if ($selectedSiswaId) {
            $jumlahSiswaAktifQuery->where('id', $selectedSiswaId);
        }
        if ($selectedKelasId) {
            $jumlahSiswaAktifQuery->where('kelas_id', $selectedKelasId);
        }
        $jumlahSiswaAktif = (int) $jumlahSiswaAktifQuery->count();

        $chartStartMonth = $now->copy()->startOfMonth()->subMonths(11);
        $chartEndMonth = $now->copy()->startOfMonth();

        if ($periodeMulai || $periodeSelesai) {
            $chartStartMonth = Carbon::parse($periodeMulai ?: $periodeSelesai)->startOfMonth();
            $chartEndMonth = Carbon::parse($periodeSelesai ?: $periodeMulai)->startOfMonth();

            if ($chartStartMonth->gt($chartEndMonth)) {
                $temp = $chartStartMonth;
                $chartStartMonth = $chartEndMonth;
                $chartEndMonth = $temp;
            }

            if ($chartStartMonth->diffInMonths($chartEndMonth) >= 24) {
                $chartStartMonth = $chartEndMonth->copy()->subMonths(23);
            }
        }

        $tagihanBulananRaw = $applyTagihanFilter(
            Tagihan::query()
                ->selectRaw('YEAR(tanggal_tagihan) as tahun, MONTH(tanggal_tagihan) as bulan, SUM(nominal_tagihan) as total')
                ->whereDate('tanggal_tagihan', '>=', $chartStartMonth->toDateString())
                ->whereDate('tanggal_tagihan', '<=', $chartEndMonth->copy()->endOfMonth()->toDateString())
                ->groupByRaw('YEAR(tanggal_tagihan), MONTH(tanggal_tagihan)'),
            false
        )->get();

        $pembayaranBulananRaw = $applyPembayaranFilter(
            Pembayaran::query()
                ->selectRaw('YEAR(tanggal_bayar) as tahun, MONTH(tanggal_bayar) as bulan, SUM(nominal_bayar) as total')
                ->whereDate('tanggal_bayar', '>=', $chartStartMonth->toDateString())
                ->whereDate('tanggal_bayar', '<=', $chartEndMonth->copy()->endOfMonth()->toDateString())
                ->groupByRaw('YEAR(tanggal_bayar), MONTH(tanggal_bayar)'),
            false
        )->get();

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

        $cursor = $chartStartMonth->copy();
        while ($cursor->lte($chartEndMonth)) {
            $key = $cursor->format('Y-m');
            $chartLabels[] = $cursor->translatedFormat('M Y');
            $chartTargetTagihan[] = $tagihanMap[$key] ?? 0;
            $chartRealisasiPembayaran[] = $pembayaranMap[$key] ?? 0;
            $cursor->addMonth();
        }

        $statusTagihan = [
            'lunas' => (int) $applyTagihanFilter(Tagihan::query()->where('status', 'lunas'), false)->count(),
            'cicil' => (int) $applyTagihanFilter(Tagihan::query()->where('status', 'cicil'), false)->count(),
            'belum_bayar' => (int) $applyTagihanFilter(Tagihan::query()->where('status', 'belum_bayar'), false)->count(),
        ];

        $totalStatusTagihan = array_sum($statusTagihan);

        $tagihanPerJenis = DB::table('tagihan as t')
            ->select('t.jenis_pembayaran_id', DB::raw('SUM(t.nominal_tagihan) as total_tagihan'))
            ->groupBy('t.jenis_pembayaran_id');

        if ($selectedSiswaId) {
            $tagihanPerJenis->where('t.siswa_id', $selectedSiswaId);
        }
        if ($selectedKelasId) {
            $tagihanPerJenis->join('siswa as s', 's.id', '=', 't.siswa_id')
                ->where('s.kelas_id', $selectedKelasId);
        }
        if ($selectedStatus) {
            $tagihanPerJenis->where('t.status', $selectedStatus);
        }
        if ($periodeMulai && $periodeSelesai) {
            $tagihanPerJenis->whereBetween('t.tanggal_tagihan', [$periodeMulai, $periodeSelesai]);
        } elseif ($periodeMulai) {
            $tagihanPerJenis->whereDate('t.tanggal_tagihan', '>=', $periodeMulai);
        } elseif ($periodeSelesai) {
            $tagihanPerJenis->whereDate('t.tanggal_tagihan', '<=', $periodeSelesai);
        }

        $pembayaranPerJenis = DB::table('pembayaran as p')
            ->select('p.jenis_pembayaran_id', DB::raw('SUM(p.nominal_bayar) as total_bayar'))
            ->whereIn('p.status', ['lunas', 'cicil'])
            ->groupBy('p.jenis_pembayaran_id');

        if ($selectedSiswaId) {
            $pembayaranPerJenis->where('p.siswa_id', $selectedSiswaId);
        }
        if ($selectedKelasId) {
            $pembayaranPerJenis->join('siswa as s2', 's2.id', '=', 'p.siswa_id')
                ->where('s2.kelas_id', $selectedKelasId);
        }
        if ($selectedStatus) {
            $pembayaranPerJenis->join('tagihan as t2', 't2.id', '=', 'p.tagihan_id')
                ->where('t2.status', $selectedStatus);
        }
        if ($periodeMulai && $periodeSelesai) {
            $pembayaranPerJenis->whereBetween('p.tanggal_bayar', [$periodeMulai, $periodeSelesai]);
        } elseif ($periodeMulai) {
            $pembayaranPerJenis->whereDate('p.tanggal_bayar', '>=', $periodeMulai);
        } elseif ($periodeSelesai) {
            $pembayaranPerJenis->whereDate('p.tanggal_bayar', '<=', $periodeSelesai);
        }

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
            })->values();

        $filterSiswa = Siswa::with('kelas')
            ->orderBy('nama_siswa')
            ->get(['id', 'nama_siswa', 'nis', 'kelas_id']);

        $filterKelas = Kelas::query()
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas']);

        $exportRows = $applyTagihanFilter(
            Tagihan::with(['siswa.kelas', 'jenisPembayaran'])
                ->orderByDesc('tanggal_tagihan')
                ->orderByDesc('id'),
            false
        )->get();

        $exportPembayaranRows = $applyPembayaranFilter(
            Pembayaran::with(['siswa.kelas', 'jenisPembayaran', 'tagihan'])
                ->orderByDesc('tanggal_bayar')
                ->orderByDesc('id'),
            false
        )->get();

        $totalNominalTagihanFiltered = (int) $applyTagihanFilter(Tagihan::query(), false)->sum('nominal_tagihan');
        $totalSisaTagihanFiltered = (int) $applyTagihanFilter(Tagihan::query(), false)->sum('sisa_tagihan');
        $totalPembayaranFiltered = (int) $applyPembayaranFilter(Pembayaran::query(), false)->sum('nominal_bayar');

        return [
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
            'filterSiswa' => $filterSiswa,
            'filterKelas' => $filterKelas,
            'exportRows' => $exportRows,
            'exportPembayaranRows' => $exportPembayaranRows,
            'totalNominalTagihanFiltered' => $totalNominalTagihanFiltered,
            'totalSisaTagihanFiltered' => $totalSisaTagihanFiltered,
            'totalPembayaranFiltered' => $totalPembayaranFiltered,
            'filters' => [
                'siswa_id' => $selectedSiswaId,
                'kelas_id' => $selectedKelasId,
                'periode_mulai' => $periodeMulai,
                'periode_selesai' => $periodeSelesai,
                'status' => $selectedStatus,
                'preset_periode' => $selectedPreset,
            ],
        ];
    }
}
