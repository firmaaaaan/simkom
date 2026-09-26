<?php

use App\Http\Controllers\TelemetryController;
use Illuminate\Support\Facades\Route;

// Telemetri PC lab — dilindungi API key via header X-API-KEY
// (lihat TelemetryController@store). Prefix "api" ditambahkan otomatis.
Route::post('/v1/telemetry', [TelemetryController::class, 'store'])
    ->name('telemetry.store')
    ->middleware('throttle:30,1');
