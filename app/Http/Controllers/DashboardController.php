<?php

namespace App\Http\Controllers;

use App\Models\JenisPembayaran;
use App\Models\Kelas;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Services\AdminDashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    private $adminDashboardService;

    public function __construct(AdminDashboardService $adminDashboardService)
    {
        $this->adminDashboardService = $adminDashboardService;
    }

    public function index(Request $request)
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
            $selectedBulan = $request->get('bulan');
            $selectedTahun = $request->get('tahun');

            $selectedBulan = is_numeric($selectedBulan) ? (int) $selectedBulan : null;
            if (!$selectedBulan || $selectedBulan < 1 || $selectedBulan > 12) {
                $selectedBulan = null;
            }

            $selectedTahun = is_numeric($selectedTahun) ? (int) $selectedTahun : null;
            if (!$selectedTahun || $selectedTahun < 2000 || $selectedTahun > 2100) {
                $selectedTahun = null;
            }

            $totalTagihanAktifBulanIni = 0;
            $sisaTagihanBelumDibayar = 0;
            $jumlahTagihanMenunggu = 0;
            $riwayatTagihan = null;
            $totalPembayaranMasuk = 0;
            $labelPeriodeTagihan = 'Bulan Ini';
            $filterTahunOptions = [(int) $now->format('Y')];

            if ($siswa) {
                $applyTagihanPeriode = function ($query) use ($selectedBulan, $selectedTahun, $startOfMonth, $endOfMonth) {
                    if ($selectedBulan) {
                        $query->whereMonth('tanggal_tagihan', $selectedBulan);
                    }

                    if ($selectedTahun) {
                        $query->whereYear('tanggal_tagihan', $selectedTahun);
                    }

                    if (!$selectedBulan && !$selectedTahun) {
                        $query->whereBetween('tanggal_tagihan', [$startOfMonth, $endOfMonth]);
                    }

                    return $query;
                };

                $applyPembayaranPeriode = function ($query) use ($selectedBulan, $selectedTahun, $startOfMonth, $endOfMonth) {
                    if ($selectedBulan) {
                        $query->whereMonth('tanggal_bayar', $selectedBulan);
                    }

                    if ($selectedTahun) {
                        $query->whereYear('tanggal_bayar', $selectedTahun);
                    }

                    if (!$selectedBulan && !$selectedTahun) {
                        $query->whereBetween('tanggal_bayar', [$startOfMonth, $endOfMonth]);
                    }

                    return $query;
                };

                if ($selectedBulan && $selectedTahun) {
                    $labelPeriodeTagihan = Carbon::create($selectedTahun, $selectedBulan, 1)->translatedFormat('F Y');
                } elseif ($selectedTahun) {
                    $labelPeriodeTagihan = 'Tahun ' . $selectedTahun;
                } elseif ($selectedBulan) {
                    $labelPeriodeTagihan = 'Bulan ' . Carbon::create(2000, $selectedBulan, 1)->translatedFormat('F');
                }

                $totalTagihanAktifBulanIni = (int) $applyTagihanPeriode(
                    Tagihan::query()->where('siswa_id', $siswa->id)
                )->sum('nominal_tagihan');

                $sisaTagihanBelumDibayar = (int) $applyTagihanPeriode(
                    Tagihan::query()
                        ->where('siswa_id', $siswa->id)
                        ->whereIn('status', ['belum_bayar', 'cicil'])
                )->sum('sisa_tagihan');

                $jumlahTagihanMenunggu = (int) $applyTagihanPeriode(
                    Tagihan::query()
                        ->where('siswa_id', $siswa->id)
                        ->whereIn('status', ['belum_bayar', 'cicil'])
                )->count();

                $riwayatTagihan = $applyTagihanPeriode(
                    Tagihan::with('jenisPembayaran')
                        ->where('siswa_id', $siswa->id)
                )
                    ->orderByDesc('tanggal_tagihan')
                    ->orderByDesc('id')
                    ->paginate(5, ['*'], 'tagihan_page')
                    ->withQueryString();

                $totalPembayaranMasuk = (int) $applyPembayaranPeriode(
                    Pembayaran::query()
                        ->where('siswa_id', $siswa->id)
                        ->whereIn('status', ['lunas', 'cicil'])
                )->sum('nominal_bayar');

                $tahunTagihan = Tagihan::query()
                    ->where('siswa_id', $siswa->id)
                    ->whereNotNull('tanggal_tagihan')
                    ->selectRaw('YEAR(tanggal_tagihan) as tahun')
                    ->distinct()
                    ->pluck('tahun')
                    ->map(fn($tahun) => (int) $tahun)
                    ->all();

                $tahunPembayaran = Pembayaran::query()
                    ->where('siswa_id', $siswa->id)
                    ->whereNotNull('tanggal_bayar')
                    ->selectRaw('YEAR(tanggal_bayar) as tahun')
                    ->distinct()
                    ->pluck('tahun')
                    ->map(fn($tahun) => (int) $tahun)
                    ->all();

                $filterTahunOptions = collect(array_merge($tahunTagihan, $tahunPembayaran))
                    ->filter()
                    ->unique()
                    ->sortDesc()
                    ->values()
                    ->all();

                if (empty($filterTahunOptions)) {
                    $filterTahunOptions = [(int) $now->format('Y')];
                }
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
                'totalPembayaranMasuk' => $totalPembayaranMasuk,
                'labelPeriodeTagihan' => $labelPeriodeTagihan,
                'filterTahunOptions' => $filterTahunOptions,
                'filters' => [
                    'bulan' => $selectedBulan,
                    'tahun' => $selectedTahun,
                ],
            ]);
        }

        return view('dashboard', $this->adminDashboardService->getData($request));
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        if (($user->role ?? null) === 'ortu') {
            abort(403, 'Akses ditolak');
        }

        $data = $this->adminDashboardService->getData($request);
        $filename = 'dashboard-admin-' . now()->format('Ymd_His') . '.xls';

        $html = view('exports.dashboard_admin', $data)->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportOrtu(Request $request)
    {
        $user = auth()->user();
        if (($user->role ?? null) !== 'ortu') {
            abort(403, 'Akses ditolak');
        }

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

        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Data siswa belum terhubung ke akun ini.');
        }

        $selectedBulan = $request->get('bulan');
        $selectedTahun = $request->get('tahun');

        $selectedBulan = is_numeric($selectedBulan) ? (int) $selectedBulan : null;
        if (!$selectedBulan || $selectedBulan < 1 || $selectedBulan > 12) {
            $selectedBulan = null;
        }

        $selectedTahun = is_numeric($selectedTahun) ? (int) $selectedTahun : null;
        if (!$selectedTahun || $selectedTahun < 2000 || $selectedTahun > 2100) {
            $selectedTahun = null;
        }

        $applyTagihanPeriode = function ($query) use ($selectedBulan, $selectedTahun) {
            if ($selectedBulan) {
                $query->whereMonth('tanggal_tagihan', $selectedBulan);
            }
            if ($selectedTahun) {
                $query->whereYear('tanggal_tagihan', $selectedTahun);
            }
            return $query;
        };

        $applyPembayaranPeriode = function ($query) use ($selectedBulan, $selectedTahun) {
            if ($selectedBulan) {
                $query->whereMonth('tanggal_bayar', $selectedBulan);
            }
            if ($selectedTahun) {
                $query->whereYear('tanggal_bayar', $selectedTahun);
            }
            return $query;
        };

        $tagihanRows = $applyTagihanPeriode(
            Tagihan::with(['jenisPembayaran'])
                ->where('siswa_id', $siswa->id)
                ->orderByDesc('tanggal_tagihan')
                ->orderByDesc('id')
        )->get();

        $pembayaranRows = $applyPembayaranPeriode(
            Pembayaran::with(['jenisPembayaran', 'tagihan'])
                ->where('siswa_id', $siswa->id)
                ->whereIn('status', ['lunas', 'cicil'])
                ->orderByDesc('tanggal_bayar')
                ->orderByDesc('id')
        )->get();

        $totalNominalTagihan = (int) $applyTagihanPeriode(
            Tagihan::query()->where('siswa_id', $siswa->id)
        )->sum('nominal_tagihan');

        $totalSisaTagihan = (int) $applyTagihanPeriode(
            Tagihan::query()->where('siswa_id', $siswa->id)
        )->sum('sisa_tagihan');

        $totalPembayaran = (int) $applyPembayaranPeriode(
            Pembayaran::query()
                ->where('siswa_id', $siswa->id)
                ->whereIn('status', ['lunas', 'cicil'])
        )->sum('nominal_bayar');

        $filename = 'tagihan-siswa-' . preg_replace('/[^A-Za-z0-9\-]/', '-', strtolower($siswa->nama_siswa ?? 'siswa')) . '-' . now()->format('Ymd_His') . '.xls';

        $html = view('exports.dashboard_ortu_tagihan', [
            'siswa' => $siswa,
            'tagihanRows' => $tagihanRows,
            'pembayaranRows' => $pembayaranRows,
            'totalNominalTagihan' => $totalNominalTagihan,
            'totalSisaTagihan' => $totalSisaTagihan,
            'totalPembayaran' => $totalPembayaran,
            'filters' => [
                'bulan' => $selectedBulan,
                'tahun' => $selectedTahun,
            ],
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

}
