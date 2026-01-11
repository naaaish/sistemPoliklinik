<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Pasien Routes
Route::get('/pasien', fn() => view('pasien.dashboard'));
Route::get('/pasien/riwayat', fn() => view('pasien.riwayat'));
Route::get('/pasien/pemeriksaan', fn() => view('pasien.pemeriksaan'));
Route::get('/pasien/artikel', fn() => view('pasien.artikel'));
Route::get('/pasien/artikel/detail', fn() => view('pasien.artikel-detail'));



// Admin Kepegawaian Routes
Route::get('/kepegawaian', fn() => view('kepegawaian.dashboard'));
Route::get('/kepegawaian/riwayat', fn() => view('kepegawaian.riwayat'));
Route::get('/kepegawaian/pemeriksaan', fn() => view('kepegawaian.pemeriksaan'));
Route::get('/kepegawaian/pegawai', fn() => view('kepegawaian.pegawai'));
Route::get('/kepegawaian/pegawai/detail', fn() => view('kepegawaian.pegawai-detail'));
Route::get('/kepegawaian/laporan', fn() => view('kepegawaian.laporan'));

