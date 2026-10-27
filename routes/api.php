<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\testApi\testapiController;
use App\Http\Controllers\Api\MonitoringController;

// Products API
Route::apiResource('/testapi', testapiController::class);

// Monitoring API
Route::get('/monitoring', [MonitoringController::class, 'index']);
Route::get('/monitoring/lokasi', [MonitoringController::class, 'lokasi']);
