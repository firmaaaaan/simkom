<?php

namespace App\Console\Commands;

use App\Services\SimpttRuangSyncService;
use Illuminate\Console\Command;
use Throwable;

class SimpttSyncRuangCommand extends Command
{
    protected $signature = 'simptt:sync-ruang';

    protected $description = 'Sinkron master ruangan berawalan SB dari API SIMPTT ke tabel api_ruang';

    public function handle(SimpttRuangSyncService $service): int
    {
        try {
            $stats = $service->sync();
        } catch (Throwable $exception) {
            $this->error('Sinkron API ruang gagal: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Ruangan SB: %d ditemukan, %d baru, %d diperbarui, %d dilewati.',
            $stats['ditemukan'],
            $stats['baru'],
            $stats['diperbarui'],
            $stats['dilewati']
        ));

        return self::SUCCESS;
    }
}
