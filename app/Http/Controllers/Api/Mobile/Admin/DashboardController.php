<?php

namespace App\Http\Controllers\Api\Mobile\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $dashboardService;

    public function __construct(AdminDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request): JsonResponse
    {
        $data = $this->dashboardService->getData($request);

        return response()->json([
            'message' => 'Data dashboard berhasil diambil.',
            'data' => [
                'summary' => [
                    'total_tagihan_bulan_ini' => $data['totalTagihanBulanIni'],
                    'total_pembayaran_masuk' => $data['totalPembayaranMasuk'],
                    'jumlah_siswa_belum_lunas' => $data['jumlahSiswaBelumLunas'],
                    'jumlah_siswa_aktif' => $data['jumlahSiswaAktif'],
                ],
                'chart' => [
                    'labels' => $data['chartLabels'],
                    'target_tagihan' => $data['chartTargetTagihan'],
                    'realisasi_pembayaran' => $data['chartRealisasiPembayaran'],
                ],
                'status_tagihan' => $data['statusTagihan'],
                'total_status_tagihan' => $data['totalStatusTagihan'],
                'progress_per_jenis' => $data['progressPerJenis'],
                'filters' => $data['filters'],
                'filter_options' => [
                    'siswa' => $data['filterSiswa'],
                    'kelas' => $data['filterKelas'],
                ],
            ],
        ]);
    }
}
