<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JenisPembayaranController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
})->name('login');

Route::get('/dashboard', function () {
    return view('dashboard2');
});

/* AUTH */
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/* DASHBOARD (sementara) */
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard2');
    })->name('dashboard');


    Route::resource('users', UserController::class);
    Route::resource('kelas', KelasController::class);
    Route::get('/kelas-list', function () {
    return \App\Models\Kelas::select('id','nama_kelas')->get();
});
    Route::resource('siswa', SiswaController::class);
    Route::resource('jenis-pembayaran', JenisPembayaranController::class);
    Route::resource('pembayaran', PembayaranController::class)->only(['index']);
});
