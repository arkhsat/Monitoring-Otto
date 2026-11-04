<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\testApi\testapiController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\OnlineMonitoringApiController;
use App\Http\Controllers\Api\LokasiApiController;

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
