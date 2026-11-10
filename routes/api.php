<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\testApi\testapiController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\OnlineMonitoringApiController;
use App\Http\Controllers\Api\LokasiApiController;
use App\Http\Controllers\Api\AuthApiController;

// Public Authentication API (menggunakan middleware web agar session cookie tersimpan di browser)
Route::middleware('web')->group(function () {
    Route::post('/login', [AuthApiController::class, 'login'])->name('api.login');
});

// Protected User APIs
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthApiController::class, 'me'])->name('api.me');
    Route::post('/logout', [AuthApiController::class, 'logout'])->name('api.logout');
});

// Products API
Route::apiResource('/testapi', testapiController::class);

// Monitoring API (Financial Reconciliation Data)
Route::get('/monitoring', [MonitoringController::class, 'index']);
Route::get('/monitoring/lokasi', [MonitoringController::class, 'lokasi']);

// Lokasi Master Data RESTful API
Route::apiResource('/lokasi', LokasiApiController::class)->names([
    'index' => 'api.lokasi.index',
    'store' => 'api.lokasi.store',
    'show' => 'api.lokasi.show',
    'update' => 'api.lokasi.update',
    'destroy' => 'api.lokasi.destroy',
]);

// Online Network Monitoring APIs
Route::prefix('online-monitoring')->group(function () {
    Route::get('/', [OnlineMonitoringApiController::class, 'index'])->name('api.online-monitoring.index');
    Route::post('/ping', [OnlineMonitoringApiController::class, 'ping'])->name('api.online-monitoring.ping');
    Route::get('/{id}', [OnlineMonitoringApiController::class, 'show'])->name('api.online-monitoring.show');
    Route::get('/{id}/history', [OnlineMonitoringApiController::class, 'history'])->name('api.online-monitoring.history');
    Route::get('/{id}/downtime', [OnlineMonitoringApiController::class, 'downtime'])->name('api.online-monitoring.downtime');
});

// Cronjob Status Monitoring API (Untuk trigger via HTTP / Webcron / Dashboard / Scheduler)
Route::match(['get', 'post'], '/cron/status-monitoring', [OnlineMonitoringApiController::class, 'cronStatus'])->name('api.cron.status-monitoring');

// cronjob testing 
Route::get('/cron-test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Cron API berhasil diakses',
        'server' => 'Windows Laragon',
        'timestamp' => now()->toDateTimeString(),
    ]);
});

