<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RiwayatController;

/*
|--------------------------------------------------------------------------
| PUBLIC PAGES
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang', [HomeController::class, 'tentang'])->name('tentang');
Route::get('/artikel', [HomeController::class, 'artikelIndex'])->name('artikel.index');


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class,'showLogin'])->name('login');
Route::post('/login', [AuthController::class,'login'])->name('login.process');
Route::post('/logout', [AuthController::class,'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| RIWAYAT (PROTECTED)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/riwayat', [RiwayatController::class,'index'])->name('pasien.riwayat');
});

/*
|--------------------------------------------------------------------------
|  ADMIN POLI ROUTES
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\AdminPoli\DashboardController;
use App\Http\Controllers\AdminPoli\PendaftaranController;

Route::prefix('adminpoli')->name('adminpoli.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // pendaftaran
    Route::get('/pendaftaran/create', [PendaftaranController::class, 'create'])->name('pendaftaran.create');
    Route::post('/pendaftaran', [PendaftaranController::class, 'store'])->name('pendaftaran.store');

    // (optional untuk autofill)
    Route::get('/api/pegawai/{nip}', [PendaftaranController::class, 'getPegawaiByNip'])->name('api.pegawai');
});
