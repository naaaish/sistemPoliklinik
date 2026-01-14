<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\Kepegawaian\KDashboardController;
use App\Http\Controllers\Kepegawaian\PegawaiController;
use App\Http\Controllers\Kepegawaian\KRiwayatController;
use App\Http\Controllers\Kepegawaian\LaporanController;

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

Route::middleware(['ensurePegawai'])->group(function () {
    Route::get('/pasien/riwayat', [RiwayatController::class, 'index'])->name('pasien.riwayat');
});

/*
|--------------------------------------------------------------------------
| PROTECTED
|--------------------------------------------------------------------------
*/
Route::middleware(['ensurePegawai'])->group(function () {

    Route::get('/pasien/riwayat', [RiwayatController::class, 'index'])
        ->name('pasien.riwayat');

    Route::get('/poliklinik/dashboard', [RiwayatController::class, 'dashboard'])
        ->name('poliklinik.dashboard');

    Route::get('/kepegawaian/dashboard', [RiwayatController::class, 'dashboard'])
        ->name('kepegawaian.dashboard');

});

/*
|--------------------------------------------------------------------------
|  ADMIN POLI ROUTES
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\AdminPoli\DashboardController as AdminPoliDashboardController;
use App\Http\Controllers\AdminPoli\PendaftaranController;

Route::prefix('adminpoli')->name('adminpoli.')->group(function () {
    Route::get('/dashboard', [AdminPoliDashboardController::class, 'index'])->name('dashboard');

    // pendaftaran
    Route::get('/pendaftaran/create', [PendaftaranController::class, 'create'])->name('pendaftaran.create');
    Route::post('/pendaftaran', [PendaftaranController::class, 'store'])->name('pendaftaran.store');

    // (optional untuk autofill)
    Route::get('/api/pegawai/{nip}', [PendaftaranController::class, 'getPegawaiByNip'])->name('api.pegawai');
});


/*
|--------------------------------------------------------------------------
|  ADMIN KEPEGAWAIAN ROUTES
|--------------------------------------------------------------------------
*/


Route::middleware(['auth', 'ensureKepegawaian'])->prefix('kepegawaian')->group(function () {


        Route::get('/dashboard', [KDashboardController::class, 'index'])
            ->name('dashboard');
    // Dashboard
    Route::get('/dashboard', [KDashboardController::class, 'index'])
        ->name('kepegawaian.dashboard');

    // Data Pegawai
    Route::get('/pegawai', [PegawaiController::class, 'index'])
        ->name('kepegawaian.pegawai');

    // Riwayat Pemeriksaan
    Route::get('/riwayat', [KRiwayatController::class, 'index'])
        ->name('kepegawaian.riwayat');

    // Laporan
    Route::get('/laporan', [LaporanController::class, 'index'])
        ->name('kepegawaian.laporan');

});