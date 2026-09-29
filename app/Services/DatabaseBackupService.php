<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DatabaseBackupService
{
    /**
     * Versi format file backup. Naikkan angka ini bila struktur JSON berubah
     * agar file backup lama bisa ditolak dengan pesan yang jelas.
     */
    public const FORMAT_VERSION = 1;

    /**
     * Tabel sistem yang TIDAK ikut dibackup:
     * - sessions: agar login admin yang sedang berjalan tidak terputus saat restore.
     * - cache/jobs: data sementara, bisa dibuat ulang.
     * - password_reset_tokens/failed_jobs/job_batches: data operasional framework.
     */
    public const EXCLUDED_TABLES = [
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
    ];

    /**
     * Batas parameter binding per statement. Dipakai konservatif agar aman
     * untuk SQLite versi lama (batas 999) maupun baru (batas 32766).
     */
    private const MAX_BINDINGS = 900;

    /**
     * Daftar tabel yang ikut dibackup (tabel aplikasi, tanpa tabel sistem).
     *
     * @return list<string>
     */
    public function tables(): array
    {
        $tables = Schema::getTableListing(null, false);

        return array_values(array_diff($tables, self::EXCLUDED_TABLES));
    }

    /**
     * Dump seluruh tabel aplikasi ke array siap-encode JSON.
     *
     * @return array<string, mixed>
     */
    public function export(): array
    {
        $tables = [];

        foreach ($this->tables() as $table) {
            $tables[$table] = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
        }

        return [
            'app' => config('app.name'),
            'format_version' => self::FORMAT_VERSION,
            'db_driver' => DB::connection()->getDriverName(),
            'created_at' => now()->toIso8601String(),
            'tables' => $tables,
        ];
    }

    /**
     * Ringkasan jumlah baris per tabel untuk ditampilkan di halaman backup.
     *
     * @return array{tables: int, rows: int, details: array<string, int>}
     */
    public function summary(): array
    {
        $details = [];
        $rows = 0;

        foreach ($this->tables() as $table) {
            $count = (int) DB::table($table)->count();
            $details[$table] = $count;
            $rows += $count;
        }

        return [
            'tables' => count($details),
            'rows' => $rows,
            'details' => $details,
        ];
    }

    /**
     * Folder penyimpanan file backup otomatis (di luar folder public).
     */
    public static function backupDirectory(): string
    {
        return storage_path('app/backups');
    }

    /**
     * Daftar file backup otomatis, terbaru di atas.
     *
     * @return list<array{name: string, path: string, size: int, modified_at: string}>
     */
    public static function files(): array
    {
        $dir = self::backupDirectory();

        if (! is_dir($dir)) {
            return [];
        }

        $files = [];

        foreach (glob($dir.'/backup-*.json') ?: [] as $path) {
            $files[] = [
                'name' => basename($path),
                'path' => $path,
                'size' => (int) filesize($path),
                'modified_at' => date('Y-m-d H:i:s', (int) filemtime($path)),
            ];
        }

        usort($files, fn (array $a, array $b) => strcmp($b['name'], $a['name']));

        return $files;
    }

    /**
     * Validasi nama file backup (mencegah path traversal), kembalikan path absolut.
     *
     * @throws RuntimeException bila nama file tidak valid atau tidak ditemukan.
     */
    public static function filePath(string $filename): string
    {
        $filename = basename($filename);

        if (! preg_match('/^backup-[0-9]{4}-[0-9]{2}-[0-9]{2}(-[0-9]{6})?\.json$/', $filename)) {
            throw new RuntimeException('Nama file backup tidak valid.');
        }

        $path = self::backupDirectory().DIRECTORY_SEPARATOR.$filename;

        if (! is_file($path)) {
            throw new RuntimeException('File backup tidak ditemukan.');
        }

        return $path;
    }

    /**
     * Tulis payload backup ke file JSON di path yang diberikan.
     *
     * @param  array<string, mixed>  $payload
     */
    public function writeToFile(array $payload, string $path): void
    {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new RuntimeException('Gagal mengubah data backup menjadi JSON: '.json_last_error_msg());
        }

        if (file_put_contents($path, $json) === false) {
            throw new RuntimeException('Gagal menulis file backup ke storage.');
        }
    }

    /**
     * Validasi struktur file backup sebelum restore dijalankan.
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws RuntimeException bila format tidak didukung.
     */
    public function validatePayload(array $payload): void
    {
        $version = $payload['format_version'] ?? null;

        if (! is_numeric($version) || (int) $version !== self::FORMAT_VERSION) {
            throw new RuntimeException(
                'Format file backup tidak didukung (format_version: '.($version ?? 'tidak ada').', didukung: '.self::FORMAT_VERSION.').'
            );
        }

        $tables = $payload['tables'] ?? null;

        if (! is_array($tables) || $tables === []) {
            throw new RuntimeException('File backup tidak berisi data tabel.');
        }

        foreach ($tables as $table => $rows) {
            if (! is_string($table) || ! is_array($rows)) {
                throw new RuntimeException('Struktur file backup tidak valid.');
            }

            foreach ($rows as $row) {
                if (! is_array($row)) {
                    throw new RuntimeException('Struktur file backup tidak valid pada tabel "'.$table.'".');
                }
            }
        }

        // Tabel yang ada di file tapi tidak dikenal sistem saat ini diabaikan
        // (allowlist), namun kolom yang tidak sesuai schema menandakan file berasal
        // dari versi aplikasi berbeda — tolak agar data tidak rusak.
        foreach (array_intersect(array_keys($tables), $this->tables()) as $table) {
            $columns = Schema::getColumnListing($table);

            foreach ($tables[$table] as $row) {
                $unknown = array_diff(array_keys($row), $columns);

                if ($unknown !== []) {
                    throw new RuntimeException(
                        'File backup tidak sesuai dengan struktur database (kolom tidak dikenal: '
                        .implode(', ', $unknown).' pada tabel "'.$table.'").'
                    );
                }
            }
        }
    }

    /**
     * Timpa seluruh data aplikasi dengan isi file backup, di dalam satu
     * transaksi: gagal di tengah = semua perubahan dibatalkan.
     *
     * @param  array<string, mixed>  $payload
     * @return array{tables: int, rows: int}
     */
    public function restore(array $payload): array
    {
        $this->validatePayload($payload);

        $tables = array_values(array_intersect(
            array_keys($payload['tables']),
            $this->tables()
        ));

        if ($tables === []) {
            throw new RuntimeException('File backup tidak berisi tabel yang dikenal aplikasi ini.');
        }

        // PRAGMA foreign_keys (SQLite) hanya berlaku bila dijalankan DI LUAR
        // transaksi, jadi dimatikan sebelum transaksi dimulai.
        Schema::disableForeignKeyConstraints();

        try {
            return DB::transaction(function () use ($payload, $tables) {
                [$parentsFirst, $childrenFirst] = $this->dependencyOrder($tables);

                foreach ($childrenFirst as $table) {
                    DB::table($table)->truncate();
                }

                $inserted = 0;

                foreach ($parentsFirst as $table) {
                    $columns = Schema::getColumnListing($table);
                    $chunkSize = max(1, intdiv(self::MAX_BINDINGS, max(1, count($columns))));

                    foreach (array_chunk($payload['tables'][$table], $chunkSize) as $chunk) {
                        $clean = array_map(fn (array $row) => array_intersect_key($row, array_flip($columns)), $chunk);

                        DB::table($table)->insert($clean);

                        $inserted += count($clean);
                    }
                }

                return [
                    'tables' => count($tables),
                    'rows' => $inserted,
                ];
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Urutkan tabel berdasarkan foreign key:
     * - parents-first: dipakai saat INSERT (tabel yang dirujuk harus terisi dulu).
     * - children-first: dipakai saat TRUNCATE (penghapus harus kosong lebih dulu).
     *
     * Pemetaan dependency tetap dijalankan meski FK sedang dimatikan, karena
     * penonaktifan PRAGMA tidak berlaku di dalam transaksi (mis. saat test).
     *
     * @param  list<string>  $tables
     * @return array{0: list<string>, 1: list<string>}
     */
    private function dependencyOrder(array $tables): array
    {
        $known = array_flip($tables);
        $dependencies = [];

        foreach ($tables as $table) {
            $dependencies[$table] = [];

            foreach (Schema::getForeignKeys($table) as $foreignKey) {
                $referenced = $foreignKey['foreign_table'] ?? null;

                // Referensi diri sendiri diabaikan agar tidak membentuk siklus.
                if ($referenced !== null && $referenced !== $table && isset($known[$referenced])) {
                    $dependencies[$table][] = $referenced;
                }
            }
        }

        $parentsFirst = [];
        $remaining = $dependencies;

        while ($remaining !== []) {
            $ready = [];

            foreach ($remaining as $table => $parents) {
                if (array_diff($parents, $parentsFirst) === []) {
                    $ready[] = $table;
                }
            }

            if ($ready === []) {
                // Siklus foreign key: pakai urutan awal (FK dimatikan di produksi
                // membuat urutan tidak kritis).
                $parentsFirst = array_merge($parentsFirst, array_keys($remaining));
                break;
            }

            foreach ($ready as $table) {
                unset($remaining[$table]);
                $parentsFirst[] = $table;
            }
        }

        return [$parentsFirst, array_reverse($parentsFirst)];
    }
}
