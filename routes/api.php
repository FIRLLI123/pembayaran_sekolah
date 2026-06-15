<?php

use App\Http\Controllers\Api\Mobile\Admin\AuthController as MobileAdminAuthController;
use App\Http\Controllers\Api\Mobile\Admin\DashboardController as MobileAdminDashboardController;
use App\Http\Controllers\Api\Mobile\Admin\JenisPembayaranController as MobileAdminJenisPembayaranController;
use App\Http\Controllers\Api\Mobile\Admin\KelasController as MobileAdminKelasController;
use App\Http\Controllers\Api\Mobile\Admin\OrtuController as MobileAdminOrtuController;
use App\Http\Controllers\Api\Mobile\Admin\PembayaranController as MobileAdminPembayaranController;
use App\Http\Controllers\Api\Mobile\Admin\ReportController as MobileAdminReportController;
use App\Http\Controllers\Api\Mobile\Admin\RiwayatKelasSiswaController as MobileAdminRiwayatKelasSiswaController;
use App\Http\Controllers\Api\Mobile\Admin\SiswaController as MobileAdminSiswaController;
use App\Http\Controllers\Api\Mobile\Admin\TagihanController as MobileAdminTagihanController;
use App\Http\Controllers\Api\Mobile\Admin\UserController as MobileAdminUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/reminder-tagihan', function () {
    return [
        [
            'nis' => '2024001',
            'nama' => 'Ahmad Fauzi',
            'kelas' => 'XI IPS 1',
            'no_wa' => '628123456789',
            'tagihan' => [
                [
                    'jenis' => 'SPP',
                    'periode' => 'May 2026',
                    'sisa' => 250000,
                    'status' => 'belum bayar',
                ],
            ],
        ],
    ];
});

Route::prefix('mobile/admin')->group(function () {
    Route::post('/login', [MobileAdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'role.api:admin,petugas'])->group(function () {
        Route::get('/dashboard', [MobileAdminDashboardController::class, 'index']);
        Route::get('/me', [MobileAdminAuthController::class, 'me']);
        Route::post('/logout', [MobileAdminAuthController::class, 'logout']);

        Route::apiResource('/kelas', MobileAdminKelasController::class);
        Route::apiResource('/siswa', MobileAdminSiswaController::class);
        Route::apiResource('/jenis-pembayaran', MobileAdminJenisPembayaranController::class);
        Route::apiResource('/ortu', MobileAdminOrtuController::class);
        Route::apiResource('/users', MobileAdminUserController::class);

        Route::get('/tagihan', [MobileAdminTagihanController::class, 'index']);
        Route::get('/tagihan/status-bulan/{tahun}', [MobileAdminTagihanController::class, 'statusBulan']);
        Route::get('/tagihan/{siswa}/detail', [MobileAdminTagihanController::class, 'detail']);
        Route::get('/tagihan/{siswa}/total-belum-lunas', [MobileAdminTagihanController::class, 'totalBelumLunas']);
        Route::post('/tagihan/generate-spp', [MobileAdminTagihanController::class, 'generateSpp']);
        Route::post('/tagihan/generate-custom', [MobileAdminTagihanController::class, 'generateCustom']);
        Route::delete('/tagihan/generated', [MobileAdminTagihanController::class, 'deleteGenerated']);

        Route::get('/pembayaran', [MobileAdminPembayaranController::class, 'index']);
        Route::get('/pembayaran/verifikasi', [MobileAdminPembayaranController::class, 'verifikasi']);
        Route::get('/pembayaran/{pembayaran}/kwitansi', [MobileAdminPembayaranController::class, 'kwitansi']);
        Route::post('/pembayaran/tagihan/{tagihan}/bayar', [MobileAdminPembayaranController::class, 'bayar']);
        Route::post('/pembayaran/siswa/{siswa}/multi-bayar', [MobileAdminPembayaranController::class, 'multiBayar']);
        Route::post('/pembayaran/{pembayaran}/approve', [MobileAdminPembayaranController::class, 'approve']);
        Route::post('/pembayaran/{pembayaran}/reject', [MobileAdminPembayaranController::class, 'reject']);
        Route::delete('/pembayaran/{pembayaran}', [MobileAdminPembayaranController::class, 'destroy']);

        Route::get('/information/riwayat-siswa', [MobileAdminRiwayatKelasSiswaController::class, 'index']);
        Route::post('/siswa/generate-kenaikan', [MobileAdminRiwayatKelasSiswaController::class, 'generateKenaikan']);

        Route::post('/reports/export-excel', [MobileAdminReportController::class, 'exportExcel']);
    });
});
