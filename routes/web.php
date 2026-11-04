<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\OnlineMonitoringController;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/login', [AuthController::class, 'showLoginForm']);
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Logout Route (supports both POST and GET for seamless UX)
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/main', [DashboardController::class, 'index'])->name('dashboard.main');
    // Route::get('/dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
    Route::get('/online-monitoring', [OnlineMonitoringController::class, 'index'])->name('dashboard.online');

    // Lokasi CRUD Routes
    Route::get('/lokasi', [LokasiController::class, 'index'])->name('dashboard.lokasi');
    Route::post('/lokasi', [LokasiController::class, 'store'])->name('dashboard.lokasi.store');
    Route::put('/lokasi/{id}', [LokasiController::class, 'update'])->name('dashboard.lokasi.update');
    Route::delete('/lokasi/{id}', [LokasiController::class, 'destroy'])->name('dashboard.lokasi.destroy');
});
