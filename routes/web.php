<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JenisPembayaranController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\OrtuController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RiwayatKelasSiswaController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/* AUTH */
Route::get('/', function () {
    return view('auth.login');
});

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/* AUTHENTICATED ROUTES */
Route::middleware('auth')->group(function () {

    /* DASHBOARD */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    Route::get('/dashboard2/export', [DashboardController::class, 'exportOrtu'])->name('dashboard2.export');

    /* PROFILE */
    Route::post('/profile/ubah-password', [ProfileController::class, 'ubahPassword'])->name('profile.ubahPassword');
    Route::post('/profile/ubah-informasi', [ProfileController::class, 'ubahInformasi'])->name('profile.ubahInformasi');

    /* MASTER */
    Route::resource('users', UserController::class);
    Route::resource('kelas', KelasController::class);
    Route::get('/kelas-list', fn() => \App\Models\Kelas::select('id', 'nama_kelas')->get());
    Route::resource('jenis-pembayaran', JenisPembayaranController::class);
    Route::get('/information/riwayat-siswa', [RiwayatKelasSiswaController::class, 'index'])->name('information.riwayat-siswa.index');

    /* SISWA */
    Route::post('/siswa/update-foto', [SiswaController::class, 'updateFoto'])->name('siswa.updateFoto');
    Route::post('/siswa/generate-kenaikan', [SiswaController::class, 'generateKenaikan'])->name('siswa.generateKenaikan');
    Route::resource('siswa', SiswaController::class);

    /* ORTU — route spesifik HARUS sebelum resource */
    Route::get('/ortu/riwayat-pembayaran', [TagihanController::class, 'ortuRiwayat'])->name('ortu.riwayat');
    Route::post('/ortu/tagihan/{id}/bayar', [TagihanController::class, 'ortuBayarTagihan'])->name('ortu.tagihan.bayar');
    Route::resource('ortu', OrtuController::class);

    /* PEMBAYARAN — route spesifik HARUS sebelum resource */
    Route::get('/pembayaran/verifikasi', [PembayaranController::class, 'verifikasi'])->name('pembayaran.verifikasi');
    Route::get('/pembayaran/export', [PembayaranController::class, 'export'])->name('pembayaran.export');
    Route::get('/pembayaran/{id}/kwitansi', [PembayaranController::class, 'kwitansi'])->name('pembayaran.kwitansi');
    Route::post('/pembayaran/{id}/approve', [PembayaranController::class, 'approve'])->name('pembayaran.approve');
    Route::post('/pembayaran/{id}/reject', [PembayaranController::class, 'reject'])->name('pembayaran.reject');
    Route::delete('/pembayaran/{id}', [PembayaranController::class, 'destroy'])->name('pembayaran.destroy');
    Route::resource('pembayaran', PembayaranController::class)->only(['index']);

    /* TAGIHAN — route spesifik HARUS sebelum route berparameter */
    Route::get('/tagihan/custom', [TagihanController::class, 'createCustom'])->name('tagihan.custom');
    Route::post('/tagihan/custom', [TagihanController::class, 'storeCustom'])->name('tagihan.custom.store');
    Route::get('/tagihan/total-belum-lunas/{siswaId}', [TagihanController::class, 'totalBelumLunas']);
    Route::get('/tagihan/status-bulan/{tahun}', [TagihanController::class, 'statusBulan']);
    Route::get('/tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
    Route::get('/tagihan/{siswaId}/detail', [TagihanController::class, 'detail'])->name('tagihan.detail');
    Route::get('/tagihan/{siswaId}/detail-ajax', [TagihanController::class, 'detailAjax'])->name('tagihan.detailAjax');
    Route::post('/tagihan/{id}/bayar', [TagihanController::class, 'bayar'])->name('tagihan.bayar');
    Route::post('/tagihan/multi-bayar/{siswa}', [TagihanController::class, 'multiBayar'])->name('tagihan.multiBayar');
    Route::post('/tagihan/hapus-generated', [TagihanController::class, 'hapusGenerated'])->name('tagihan.hapusGenerated');
    Route::post('/generate-spp', [TagihanController::class, 'generateSPP'])->name('generate.spp');

});
