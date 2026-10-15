<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto backup bulanan: tanggal 1 tiap bulan pukul 00.10.
// Butuh cron server: * * * * * php artisan schedule:run
Schedule::command('backup:run')
    ->monthlyOn(1, '00:10')
    ->withoutOverlapping();

// Monitoring PC: tandai PC yang tidak kirim telemetri > 30 detik sebagai offline.
// Sub-minute schedule butuh `php artisan schedule:work` yang berjalan.
Schedule::command('monitoring:check-offline')->everyFiveSeconds();
