<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\AdminPoli\ObatController;
use App\Http\Controllers\AdminPoli\DashboardController as AdminPoliDashboardController;
use App\Http\Controllers\AdminPoli\PendaftaranController;
use App\Http\Controllers\AdminPoli\PemeriksaanController;

use App\Http\Controllers\Kepegawaian\KDashboardController;
use App\Http\Controllers\Kepegawaian\PegawaiController;
use App\Http\Controllers\Kepegawaian\KRiwayatController;
use App\Http\Controllers\Kepegawaian\LaporanController;
use App\Http\Controllers\AdminPoli\DiagnosaController;
use App\Http\Controllers\AdminPoli\DiagnosaK3Controller;
use App\Http\Controllers\Pasien\DetailPemeriksaanController;
use App\Http\Controllers\Kepegawaian\DetailRiwayatController;

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

    // (optional untuk autofill)
    Route::get('/api/pegawai/{nip}', [PendaftaranController::class, 'getPegawaiByNip'])->name('api.pegawai');

    Route::resource('obat', ObatController::class)->except(['show']);
    Route::post('obat/import', [\App\Http\Controllers\AdminPoli\ObatController::class, 'import'])->name('obat.import');
    Route::get('obat/export', [\App\Http\Controllers\AdminPoli\ObatController::class, 'export'])->name('obat.export');

    // Diagnosa
    Route::post('diagnosa/import', [DiagnosaController::class, 'import'])->name('diagnosa.import');
    Route::get('diagnosa/export', [DiagnosaController::class, 'export'])->name('diagnosa.export');
    Route::resource('diagnosa', DiagnosaController::class)->except(['show']);

    // Diagnosa K3
    Route::post('diagnosa-k3/import', [DiagnosaK3Controller::class, 'import'])->name('diagnosak3.import');
    Route::get('diagnosa-k3/export', [DiagnosaK3Controller::class, 'export'])->name('diagnosak3.export');

    Route::resource('diagnosa-k3', DiagnosaK3Controller::class)->except(['show'])
        ->names('diagnosak3');
    Route::post('diagnosa-k3/kategori', [\App\Http\Controllers\AdminPoli\DiagnosaK3Controller::class, 'storeKategori'])->name('diagnosak3.kategori.store');
    Route::put('diagnosa-k3/kategori/{kategori}', [\App\Http\Controllers\AdminPoli\DiagnosaK3Controller::class, 'updateKategori'])->name('diagnosak3.kategori.update');
    Route::delete('diagnosa-k3/kategori/{kategori}', [\App\Http\Controllers\AdminPoli\DiagnosaK3Controller::class, 'destroyKategori'])->name('diagnosak3.kategori.destroy');
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
    Route::prefix('kepegawaian')->group(function () {
        Route::get('/laporan', [LaporanController::class, 'index'])
            ->name('kepegawaian.laporan');

        Route::get('/laporan/{jenis}', [LaporanController::class, 'detail'])
            ->name('kepegawaian.laporan.detail');
    });


    Route::get('/laporan/{jenis}/download', [LaporanController::class, 'downloadPdf'])
        ->name('kepegawaian.laporan.download');

    Route::get('/kepegawaian/riwayat/{id}', [DetailRiwayatController::class, 'show'])
    ->name('kepegawaian.riwayat.detail');
});

// Route::get('/pasien/riwayat', [RiwayatController::class, 'index'])
//     ->name('pasien.riwayat');
use App\Http\Controllers\Pasien\ArtikelController;

Route::middleware(['auth'])->group(function () {

    Route::get('/pasien/artikel', 
        [ArtikelController::class, 'index']
    )->name('pasien.artikel');

    Route::get('/pasien/artikel/{id}', 
        [ArtikelController::class, 'show']
    )->name('pasien.artikel.detail');

    Route::get('/pasien/pemeriksaan/{id}', 
        [DetailPemeriksaanController::class, 'show']
    )->name('pasien.pemeriksaan.detail');
});



// LAPORAN

Route::prefix('kepegawaian')->middleware('auth')->group(function () {

    Route::get('/laporan', [LaporanController::class, 'index'])
        ->name('kepegawaian.laporan');

    Route::get('/laporan/{jenis}', [LaporanController::class, 'detail'])
        ->name('kepegawaian.laporan.detail');

    Route::get('/laporan/{jenis}/download', [LaporanController::class, 'download'])
        ->name('kepegawaian.laporan.download');

});
