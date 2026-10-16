<?php

namespace App\Console\Commands;

use App\Models\PcMonitor;
use App\Models\PcPowerCommand;
use Illuminate\Console\Command;

class MonitoringCheckOfflineCommand extends Command
{
    protected $signature = 'monitoring:check-offline';

    protected $description = 'Tandai PC yang basi (tidak lapor telemetri) sebagai offline dan kedaluwarsakan perintah power yang basi';

    public function handle(): int
    {
        // 1. Sinkronkan kolom status: PC stale → offline agar konsisten
        //    dengan status efektif yang dihitung dari last_seen_at.
        $stale = PcMonitor::query()->stale()->update(['status' => PcMonitor::STATUS_OFFLINE]);

        // 2. Kedaluwarsakan perintah power pending yang lewat TTL —
        //    agent tidak sempat mengambilnya (mis. PC sudah dimatikan manual).
        $expired = PcPowerCommand::query()
            ->where('status', PcPowerCommand::STATUS_PENDING)
            ->where('created_at', '<', now()->subSeconds(PcPowerCommand::TTL_SECONDS))
            ->update(['status' => PcPowerCommand::STATUS_FAILED]);

        $this->info(sprintf(
            'Monitoring: %d PC ditandai offline, %d perintah power kedaluwarsa.',
            $stale,
            $expired
        ));

        return self::SUCCESS;
    }
}
