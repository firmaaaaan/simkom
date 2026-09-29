<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'backup:run';

    protected $description = 'Backup seluruh data aplikasi ke file JSON di storage/app/backups';

    public function handle(DatabaseBackupService $backup): int
    {
        $this->info('Mengumpulkan data...');

        $payload = $backup->export();

        $path = self::destinationPath();

        $backup->writeToFile($payload, $path);

        $rows = collect($payload['tables'])->sum(fn ($rows) => count($rows));

        $this->info(sprintf(
            'Backup berhasil: %s (%d tabel, %d baris, %s)',
            $path,
            count($payload['tables']),
            $rows,
            $this->humanSize((int) filesize($path))
        ));

        return self::SUCCESS;
    }

    public static function destinationPath(): string
    {
        return DatabaseBackupService::backupDirectory().'/backup-'.date('Y-m-d-His').'.json';
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }
}
