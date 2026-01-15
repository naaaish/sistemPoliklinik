<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\AdminPoli\ObatController;
use App\Http\Controllers\AdminPoli\DashboardController as AdminPoliDashboardController;
use App\Http\Controllers\AdminPoli\PendaftaranController;
use App\Http\Controllers\AdminPoli\PemeriksaanController;


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

Route::prefix('adminpoli')->name('adminpoli.')->group(function () {
    Route::get('/dashboard', [AdminPoliDashboardController::class, 'index'])->name('dashboard');

    // pendaftaran
    Route::get('/pendaftaran/create', [PendaftaranController::class, 'create'])->name('pendaftaran.create');
    Route::post('/pendaftaran', [PendaftaranController::class, 'store'])->name('pendaftaran.store');
    // pemeriksaan (HARUS bawa id pendaftaran)
    Route::get('/pemeriksaan/{pendaftaranId}/create', [PemeriksaanController::class, 'create'])
        ->name('pemeriksaan.create');

    Route::post('/pemeriksaan/{pendaftaranId}', [PemeriksaanController::class, 'store'])
        ->name('pemeriksaan.store');

    Route::resource('obat', ObatController::class)->except(['show']);
    Route::post('obat/import', [\App\Http\Controllers\AdminPoli\ObatController::class, 'import'])->name('obat.import');
    Route::get('obat/export', [\App\Http\Controllers\AdminPoli\ObatController::class, 'export'])->name('obat.export');


    // (optional untuk autofill)
    Route::get('/api/pegawai/{nip}', [PendaftaranController::class, 'getPegawaiByNip'])->name('api.pegawai');
});