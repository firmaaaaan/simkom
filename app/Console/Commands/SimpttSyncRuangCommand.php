<?php

namespace App\Console\Commands;

use App\Services\SimpttRuangSyncService;
use Illuminate\Console\Command;
use Throwable;

class SimpttSyncRuangCommand extends Command
{
    protected $signature = 'simptt:sync-ruang';

    protected $description = 'Sinkron master ruangan dari API SIMPTT ke tabel api_ruang dan laboratories';

    public function handle(SimpttRuangSyncService $service): int
    {
        try {
            $stats = $service->sync();
        } catch (Throwable $exception) {
            $this->error('Sinkron API ruang gagal: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Ruangan: %d ditemukan, %d baru, %d diperbarui, %d dilewati.',
            $stats['ditemukan'],
            $stats['baru'],
            $stats['diperbarui'],
            $stats['dilewati']
        ));

        return self::SUCCESS;
    }
}
