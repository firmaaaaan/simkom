<?php

use App\Http\Controllers\AgentCommandController;
use App\Http\Controllers\TelemetryController;
use Illuminate\Support\Facades\Route;

// Telemetri & perintah PC lab — dilindungi API key via header X-API-KEY
// (lihat TelemetryController@store & AgentCommandController).
// Prefix "api" ditambahkan otomatis oleh bootstrap/app.php.

Route::post('/v1/telemetry', [TelemetryController::class, 'store'])
    ->name('telemetry.store')
    ->middleware('throttle:30,1');

Route::get('/v1/commands', [AgentCommandController::class, 'index'])
    ->name('commands.index')
    ->middleware('throttle:60,1');

Route::post('/v1/commands/{command}/ack', [AgentCommandController::class, 'ack'])
    ->name('commands.ack')
    ->middleware('throttle:60,1');
