<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JenisPembayaranController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
})->name('login');

/* AUTH */
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/* DASHBOARD (sementara) */
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $role = auth()->user()->role ?? null;

        if ($role === 'ortu') {
            return view('dashboard2');
        }

        return view('dashboard');
    })->name('dashboard');


    Route::resource('users', UserController::class);
    Route::resource('kelas', KelasController::class);
    Route::get('/kelas-list', function () {
    return \App\Models\Kelas::select('id','nama_kelas')->get();
});
    Route::resource('siswa', SiswaController::class);
    Route::resource('jenis-pembayaran', JenisPembayaranController::class);
    Route::resource('pembayaran', PembayaranController::class)->only(['index']);
    Route::get('/pembayaran/{id}/kwitansi', [PembayaranController::class, 'kwitansi'])
        ->name('pembayaran.kwitansi');
    Route::get('/pembayaran/verifikasi', [PembayaranController::class, 'verifikasi'])->name('pembayaran.verifikasi');
    Route::post('/pembayaran/{id}/approve', [PembayaranController::class, 'approve'])->name('pembayaran.approve');
    Route::post('/pembayaran/{id}/reject', [PembayaranController::class, 'reject'])->name('pembayaran.reject');
    Route::get('/ortu/riwayat-pembayaran', [TagihanController::class, 'ortuRiwayat'])
        ->name('ortu.riwayat');
    Route::post('/ortu/tagihan/{id}/bayar', [TagihanController::class, 'ortuBayarTagihan'])
        ->name('ortu.tagihan.bayar');

    Route::get('/tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
    Route::get('/tagihan/{siswaId}/detail', [TagihanController::class, 'detail'])->name('tagihan.detail');
    Route::get('/tagihan/{siswaId}/detail-ajax', [TagihanController::class, 'detailAjax'])->name('tagihan.detailAjax');
    Route::post('/generate-spp', [TagihanController::class, 'generateSPP'])->name('generate.spp');
    Route::post('/tagihan/{id}/bayar', [TagihanController::class, 'bayar'])
    ->name('tagihan.bayar');
    Route::post('/tagihan/multi-bayar/{siswa}', [TagihanController::class, 'multiBayar'])
    ->name('tagihan.multiBayar');
    Route::get('/tagihan/total-belum-lunas/{siswaId}', [TagihanController::class, 'totalBelumLunas']);
    Route::get('/tagihan/custom', [TagihanController::class, 'createCustom'])->name('tagihan.custom');
Route::post('/tagihan/custom', [TagihanController::class, 'storeCustom'])->name('tagihan.custom.store');
});
