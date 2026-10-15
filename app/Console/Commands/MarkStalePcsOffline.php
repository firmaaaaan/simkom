<?php

namespace App\Console\Commands;

use App\Models\PcMonitor;
use Illuminate\Console\Command;

class MarkStalePcsOffline extends Command
{
    protected $signature = 'monitoring:check-offline
        {--seconds=30 : Batas detik tanpa telemetri sebelum PC dianggap offline}';

    protected $description = 'Tandai PC yang tidak mengirim telemetri lebih dari 30 detik sebagai offline';

    public function handle(): int
    {
        $seconds = max(1, (int) $this->option('seconds'));

        $count = PcMonitor::online()
            ->where(function ($query) use ($seconds) {
                $query->whereNull('last_seen_at')
                    ->orWhere('last_seen_at', '<', now()->subSeconds($seconds));
            })
            ->update(['status' => PcMonitor::STATUS_OFFLINE]);

        $this->info("{$count} PC ditandai offline (tanpa telemetri > {$seconds} detik).");

        return self::SUCCESS;
    }
}
