<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;

// HOME / DASHBOARD UTAMA (Public - bisa diakses semua orang tanpa login)
Route::get('/', [HomeController::class, 'index'])->name('home');

// PUBLIC PAGES (bisa diakses tanpa login)
Route::get('/tentang', [HomeController::class, 'tentang'])->name('tentang');
Route::get('/artikel', [HomeController::class, 'artikelIndex'])->name('artikel.index');

// AUTH ROUTES
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// RIWAYAT PEMERIKSAAN (HARUS LOGIN DULU)
Route::middleware('auth')->group(function () {
    Route::get('/riwayat', [HomeController::class, 'riwayat'])->name('riwayat.index');
});