<?php

use App\Http\Controllers\AgentCommandController;
use App\Http\Controllers\AgentReportController;
use App\Http\Controllers\TelemetryController;
use Illuminate\Support\Facades\Route;

// Jalur agent PC lab (stateless, tanpa session/login) — dilindungi API key
// via header X-API-KEY (middleware agent.apikey) + throttle per menit.
// Prefix "api" ditambahkan otomatis oleh bootstrap/app.php.
Route::middleware('agent.apikey')->group(function () {
    Route::post('/v1/telemetry', [TelemetryController::class, 'store'])
        ->name('telemetry.store')
        ->middleware('throttle:30,1');

    Route::get('/v1/commands', [AgentCommandController::class, 'index'])
        ->name('commands.index')
        ->middleware('throttle:60,1');

    Route::post('/v1/commands/{command}/ack', [AgentCommandController::class, 'ack'])
        ->name('commands.ack')
        ->middleware('throttle:60,1');

    Route::post('/v1/screenshots', [AgentReportController::class, 'screenshot'])
        ->name('agent.screenshots.store')
        ->middleware('throttle:6,1');

    Route::post('/v1/software', [AgentReportController::class, 'software'])
        ->name('agent.software.store')
        ->middleware('throttle:3,1');
});
