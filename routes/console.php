<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Monitoring PC: tandai PC yang tidak kirim telemetri > 30 detik sebagai offline.
// Sub-minute schedule butuh `php artisan schedule:work` yang berjalan.
Schedule::command('monitoring:check-offline')->everyFiveSeconds();
