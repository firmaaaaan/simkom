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

// Sinkron status PC offline & kedaluwarsakan perintah power basi.
Schedule::command('monitoring:check-offline')
    ->everyMinute()
    ->withoutOverlapping();

// Pengingat To-Do List via Telegram: jatuh tempo H-1, hari ini, & terlewat.
Schedule::command('todo:remind-due')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();
