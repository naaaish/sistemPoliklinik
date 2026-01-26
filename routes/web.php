<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\AdminPoli\ObatController;
use App\Http\Controllers\AdminPoli\DashboardController as AdminPoliDashboardController;
use App\Http\Controllers\AdminPoli\PendaftaranController;
use App\Http\Controllers\AdminPoli\PemeriksaanInputController;
use App\Http\Controllers\AdminPoli\PemeriksaanController;
use App\Http\Controllers\AdminPoli\SaranController;
use App\Http\Controllers\AdminPoli\ArtikelController as AdminPoliArtikelController;
use App\Http\Controllers\AdminPoli\LaporanController as AdminPoliLaporanController;

use App\Http\Controllers\Kepegawaian\KDashboardController;
use App\Http\Controllers\Kepegawaian\PegawaiController;
use App\Http\Controllers\Kepegawaian\KRiwayatController;
use App\Http\Controllers\Kepegawaian\LaporanController;
use App\Http\Controllers\AdminPoli\DiagnosaController;
use App\Http\Controllers\AdminPoli\DiagnosaK3Controller;
use App\Http\Controllers\Pasien\DetailPemeriksaanController;
use App\Http\Controllers\Kepegawaian\DokterPemeriksaController;
use App\Http\Controllers\Kepegawaian\DetailRiwayatController;
use App\Http\Controllers\Pasien\ArtikelController;

/*
|--------------------------------------------------------------------------
| PUBLIC PAGES
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/artikel', [HomeController::class, 'artikelIndex'])
    ->name('artikel.index.public');

Route::get('/artikel/{id_artikel}', [HomeController::class, 'artikelDetail'])
    ->name('artikel.detail.public');

Route::get('/artikel', [ArtikelController::class, 'indexPublic'])
    ->name('artikel.index.public');

Route::get('/artikel/{id}', [ArtikelController::class, 'detail'])
    ->name('artikel.detail');


Route::get('/tentang', [HomeController::class, 'tentang'])->name('tentang');


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
Route::prefix('adminpoli')
    ->name('adminpoli.')
    ->middleware(['auth', 'ensureAdminPoli'])
    ->group(function () {
        Route::get('/dashboard', [AdminPoliDashboardController::class, 'index'])->name('dashboard');

        // pendaftaran
        Route::get('/pendaftaran/create', [PendaftaranController::class, 'create'])->name('pendaftaran.create');
        Route::post('/pendaftaran', [PendaftaranController::class, 'store'])->name('pendaftaran.store');
        Route::get('/api/pegawai/search', [\App\Http\Controllers\AdminPoli\PendaftaranController::class, 'searchPegawai']);

        // pemeriksaan (HARUS bawa id pendaftaran)
        Route::get('/pemeriksaan/{pendaftaranId}/create', [PemeriksaanInputController::class, 'create'])
            ->name('pemeriksaan.create');

        Route::post('/pemeriksaan/{pendaftaranId}', [PemeriksaanInputController::class, 'store'])
            ->name('pemeriksaan.store');

        // (optional untuk autofill)
        Route::get('/api/pegawai/{nip}', [PendaftaranController::class, 'getPegawaiByNip'])->name('api.pegawai');
        Route::get('/api/pegawai/{nip}/keluarga', [PendaftaranController::class, 'getKeluargaByNip']);

        Route::resource('obat', ObatController::class)->except(['show']);
        Route::post('obat/import', [\App\Http\Controllers\AdminPoli\ObatController::class, 'import'])->name('obat.import');
        Route::get('obat/export', [\App\Http\Controllers\AdminPoli\ObatController::class, 'export'])->name('obat.export');

        // Diagnosa
        Route::post('diagnosa/import', [DiagnosaController::class, 'import'])->name('diagnosa.import');
        Route::get('diagnosa/export', [DiagnosaController::class, 'export'])->name('diagnosa.export');
        Route::resource('diagnosa', DiagnosaController::class)->except(['show']);

        Route::get('/api/diagnosa/{id}/nb', [PemeriksaanController::class, 'getNbByDiagnosa']);

        // Diagnosa K3
        Route::get('/diagnosak3', [DiagnosaK3Controller::class,'index'])->name('diagnosak3.index');

        Route::post('/diagnosak3/kategori', [DiagnosaK3Controller::class,'storeKategori'])->name('diagnosak3.kategori.store');
        Route::put('/diagnosak3/kategori/{id_nb}', [DiagnosaK3Controller::class,'updateKategori'])->name('diagnosak3.kategori.update');
        Route::delete('/diagnosak3/kategori/{id_nb}', [DiagnosaK3Controller::class,'destroyKategori'])->name('diagnosak3.kategori.destroy');

        Route::post('/diagnosak3/penyakit', [DiagnosaK3Controller::class,'storePenyakit'])->name('diagnosak3.penyakit.store');
        Route::put('/diagnosak3/penyakit/{id_nb}', [DiagnosaK3Controller::class,'updatePenyakit'])->name('diagnosak3.penyakit.update');
        Route::delete('/diagnosak3/penyakit/{id_nb}', [DiagnosaK3Controller::class,'destroyPenyakit'])->name('diagnosak3.penyakit.destroy');

        Route::post('/diagnosak3/import', [DiagnosaK3Controller::class,'import'])->name('diagnosak3.import');
        Route::get('/diagnosak3/export', [DiagnosaK3Controller::class,'export'])->name('diagnosak3.export');

        // Saran
        Route::resource('saran', SaranController::class)->except(['show']);
        Route::post('saran/import', [SaranController::class, 'import'])->name('saran.import');
        Route::get('saran/export', [SaranController::class, 'export'])->name('saran.export');

        // MENU PEMERIKSAAN PASIEN
        Route::get('/pemeriksaan',
            [PemeriksaanController::class, 'index']
        )->name('pemeriksaan.index');

        Route::get('/pemeriksaan/{pendaftaranId}',
            [PemeriksaanController::class, 'show']
        )->name('pemeriksaan.show');

        Route::get('/pemeriksaan/{pendaftaranId}/edit',
            [PemeriksaanController::class, 'edit']
        )->name('pemeriksaan.edit');

        Route::put('/pemeriksaan/{pendaftaranId}',
            [PemeriksaanController::class, 'update']
        )->name('pemeriksaan.update');

        // Artikel
        Route::get('/artikel', [AdminPoliArtikelController::class, 'index'])->name('artikel.index');
        Route::get('/artikel/create', [AdminPoliArtikelController::class, 'create'])->name('artikel.create');
        Route::post('/artikel', [AdminPoliArtikelController::class, 'store'])->name('artikel.store');
        Route::get('/artikel/{id}/edit', [AdminPoliArtikelController::class, 'edit'])->name('artikel.edit');
        Route::put('/artikel/{id}', [AdminPoliArtikelController::class, 'update'])->name('artikel.update');
        Route::delete('/artikel/{id}', [AdminPoliArtikelController::class, 'destroy'])->name('artikel.destroy');

        // upload from pdf/word → create draft → redirect edit
        Route::post('/artikel/import', [AdminPoliArtikelController::class, 'importDoc'])->name('artikel.import');

        // Laporan
        Route::get('/laporan', [AdminPoliLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/preview', [AdminPoliLaporanController::class, 'preview'])->name('laporan.preview');
        // export excel (PREVIEW: single sheet sesuai tipe yg dipreview)
        Route::get('/laporan/export', [AdminPoliLaporanController::class, 'exportExcel'])
        ->name('laporan.export');

        // export excel (INDEX: all tipe -> multi sheets)
        Route::get('/laporan/export-all', [AdminPoliLaporanController::class, 'exportExcelAll'])
        ->name('laporan.exportAll');
    }
);
/*
|--------------------------------------------------------------------------
| ADMIN KEPEGAWAIAN ROUTES 
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'ensureKepegawaian'])
    ->prefix('kepegawaian')
    ->name('kepegawaian.')
    ->group(function () {

    /* ================= DASHBOARD ================= */
    Route::get('/dashboard', [KDashboardController::class, 'index'])
        ->name('dashboard');

    /* ================= PEGAWAI ================= */
    Route::get('/pegawai', [PegawaiController::class, 'index'])
        ->name('pegawai');

    Route::get('/pegawai/{id}', [PegawaiController::class, 'show'])
        ->name('pegawai.show');

    /* ================= RIWAYAT ================= */
    Route::get('/riwayat', [KRiwayatController::class, 'index'])
        ->name('riwayat');

    Route::get('/riwayat/{id}', [DetailRiwayatController::class, 'show'])
        ->name('riwayat.detail');


    /* ================= LAPORAN ================= */
    Route::get('/laporan', [LaporanController::class, 'index'])
        ->name('laporan');

    Route::get('/laporan/{jenis}', [LaporanController::class, 'detail'])
        ->name('laporan.detail');

    Route::get('/laporan/{jenis}/download', [LaporanController::class, 'downloadPdf'])
        ->name('laporan.download');

    /* ================= DOKTER & PEMERIKSA ================= */
    Route::get('/dokter-pemeriksa', [DokterPemeriksaController::class, 'index'])
        ->name('dokter_pemeriksa.index');

    // Dokter
    Route::post('/dokter-pemeriksa/dokter', [DokterPemeriksaController::class, 'storeDokter'])
        ->name('dokter_pemeriksa.dokter.store');

    Route::put('/dokter-pemeriksa/dokter/{id}', [DokterPemeriksaController::class, 'updateDokter'])
        ->name('dokter_pemeriksa.dokter.update');

    Route::delete('/dokter-pemeriksa/dokter/{id}', [DokterPemeriksaController::class, 'destroyDokter'])
        ->name('dokter_pemeriksa.dokter.destroy');

    Route::patch('/dokter-pemeriksa/dokter/{id}/status', [DokterPemeriksaController::class, 'updateStatusDokter'])
        ->name('dokter_pemeriksa.dokter.status');

    Route::get('/dokter-pemeriksa/dokter/{id}/jadwal', [DokterPemeriksaController::class, 'jadwalDokterJson'])
        ->name('dokter_pemeriksa.dokter.jadwal');

    // Pemeriksa
    Route::post('/dokter-pemeriksa/pemeriksa', [DokterPemeriksaController::class, 'storePemeriksa'])
        ->name('dokter_pemeriksa.pemeriksa.store');

    Route::put('/dokter-pemeriksa/pemeriksa/{id}', [DokterPemeriksaController::class, 'updatePemeriksa'])
        ->name('dokter_pemeriksa.pemeriksa.update');

    Route::delete('/dokter-pemeriksa/pemeriksa/{id}', [DokterPemeriksaController::class, 'destroyPemeriksa'])
        ->name('dokter_pemeriksa.pemeriksa.destroy');

    Route::patch('/dokter-pemeriksa/pemeriksa/{id}/status', [DokterPemeriksaController::class, 'updateStatusPemeriksa'])
        ->name('dokter_pemeriksa.pemeriksa.status');

    // Jadwal gabungan
    Route::get('/dokter-pemeriksa/{tipe}/{id}/jadwal', [DokterPemeriksaController::class, 'jadwalJson'])
        ->name('dokter_pemeriksa.jadwal_json');

    Route::get('/dokter-pemeriksa/{tipe}/{id}/jadwal-view', [DokterPemeriksaController::class, 'jadwalView'])
        ->name('dokter_pemeriksa.jadwal_view');

    Route::get(
        '/kepegawaian/laporan/{jenis}/excel',
        [LaporanController::class, 'exportExcel']
    )->name('laporan.excel');

});


// Route::get('/pasien/riwayat', [RiwayatController::class, 'index'])
//     ->name('pasien.riwayat');


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

    Route::get('/riwayat/{id_pemeriksaan}', [RiwayatController::class, 'detail'])
        ->name('riwayat.detail');

});



// LAPORAN

Route::prefix('kepegawaian/laporan')
    ->middleware(['auth', 'ensureKepegawaian'])
    ->group(function () {

        Route::get('/', [LaporanController::class, 'index'])
            ->name('kepegawaian.laporan');

        Route::get('/{jenis}', [LaporanController::class, 'detail'])
            ->name('kepegawaian.laporan.detail');

        Route::get('/kepegawaian/laporan/{jenis}/excel', 
            [LaporanController::class, 'downloadExcelPegawaiPensiun']
        )->name('laporan.excel.pegawai-pensiun');

        Route::get('/dokter/excel', [LaporanController::class, 'downloadExcelDokter'])
            ->name('laporan.excel.dokter');

    Route::get('/laporan/{jenis}/pdf', [LaporanController::class, 'downloadPdf'])
       ->name('laporan.pdf');
});


// CRUD PEGAWAI
Route::prefix('pegawai')->group(function () {
    Route::get('/', [PegawaiController::class, 'index'])->name('pegawai.index');
    Route::get('/create', [PegawaiController::class, 'create'])->name('pegawai.create');
    Route::post('/store', [PegawaiController::class, 'store'])->name('pegawai.store');
    Route::get('/{nip}', [PegawaiController::class, 'show'])->name('pegawai.show');
    Route::get('/{nip}/edit', [PegawaiController::class, 'edit'])->name('pegawai.edit');
    Route::post('/{nip}/update', [PegawaiController::class, 'update'])->name('pegawai.update');

    Route::post('/kepegawaian/pegawai/import-csv',
        [PegawaiController::class, 'importCsv']
    )->name('pegawai.import.csv');

    Route::post('/pegawai/kepegawaian/pegawai/import', 
        [PegawaiController::class, 'import']
    )->name('pegawai.import');

    Route::get('/laporan/excel/pegawai-pensiun/{jenis}', [LaporanController::class, 'downloadExcelPegawaiPensiun'])->name('laporan.excel.pegawai-pensiun');
    Route::get('/laporan/excel/dokter', [LaporanController::class, 'downloadExcelDokter'])->name('laporan.excel.dokter');
    Route::get('/laporan/excel/obat', [LaporanController::class, 'downloadExcelObat'])->name('laporan.excel.obat');
    Route::get('/laporan/excel/total', [LaporanController::class, 'downloadExcelTotal'])->name('laporan.excel.total');

});
