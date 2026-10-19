<?php

use App\Http\Controllers\AgentCommandController;
use App\Http\Controllers\AgentReportController;
use App\Http\Controllers\TelemetryController;
use Illuminate\Support\Facades\Route;

// Jalur agent PC lab (stateless, tanpa session/login) — dilindungi API key
// via header X-API-KEY + throttle per-hostname (lihat AppServiceProvider):
// batas berlaku per PC agent, bukan per-IP, agar PC di belakang NAT/proxy
// tidak saling menekan kuota (penyebab last_seen tersendat → flap status).
// Prefix "api" ditambahkan otomatis oleh bootstrap/app.php.
Route::middleware('agent.apikey')->group(function () {
    Route::post('/v1/telemetry', [TelemetryController::class, 'store'])
        ->name('telemetry.store')
        ->middleware('throttle:agent-telemetry');

    Route::get('/v1/commands', [AgentCommandController::class, 'index'])
        ->name('commands.index')
        ->middleware('throttle:agent-commands');

    Route::post('/v1/commands/{command}/ack', [AgentCommandController::class, 'ack'])
        ->name('commands.ack')
        ->middleware('throttle:60,1');

    Route::post('/v1/screenshots', [AgentReportController::class, 'screenshot'])
        ->name('agent.screenshots.store')
        ->middleware('throttle:agent-screenshots');

    Route::post('/v1/software', [AgentReportController::class, 'software'])
        ->name('agent.software.store')
        ->middleware('throttle:agent-software');

    Route::post('/v1/processes', [AgentReportController::class, 'processes'])
        ->name('agent.processes.store')
        ->middleware('throttle:agent-processes');
});
