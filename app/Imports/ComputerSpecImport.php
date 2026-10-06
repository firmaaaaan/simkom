<?php

namespace App\Imports;

use App\Models\Computer;
use App\Models\Hardware;
use App\Models\Laboratory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Import spesifikasi komputer format lebar: satu baris per komputer,
 * satu kolom per kategori hardware (nama kolom asli dipertahankan sebagai
 * kategori, tanpa diformat ulang).
 *
 * Perilaku:
 * - Kode komputer dicari; bila belum ada, komputer dibuat otomatis (Aktif).
 * - Sel komponen yang terisi dicari hardware-nya berdasarkan nama + kategori
 *   (case-insensitive); bila belum ada, hardware baru dibuat (kode HW-xxx).
 * - Beberapa komponen dalam satu sel dipisah ";" atau baris baru.
 * - Hardware komputer pada file di-sync (diganti sesuai isi file).
 *   Software tidak disentuh karena file hanya berisi hardware.
 * - Baris gagal dikumpulkan ke getErrors() tanpa menggagalkan seluruh file.
 */
class ComputerSpecImport implements ToCollection
{
    /**
     * Kolom yang dianggap metadata, bukan spesifikasi.
     */
    private const METADATA_COLUMNS = [
        'kode komputer',
        'kode',
        'laboratorium',
        'lab',
        'status',
        'keterangan',
        'no',
        'nomor',
        'nomor meja',
    ];

    protected int $syncedCount = 0;
    protected int $createdComputersCount = 0;
    protected int $createdHardwareCount = 0;
    protected array $errors = [];

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            $this->errors[] = 'File tidak berisi data.';

            return;
        }

        $headings = $rows->shift()
            ->map(fn ($heading) => trim((string) $heading))
            ->values();

        $codeIndex = $headings->search(
            fn ($heading) => preg_match('/^kode(\s+komputer)?$/i', $heading) === 1
        );

        if ($codeIndex === false) {
            $this->errors[] = 'Judul kolom "Kode Komputer" tidak ditemukan pada baris pertama file.';

            return;
        }

        $normalized = $headings->map(
            fn ($heading) => strtolower(rtrim(trim($heading), '.'))
        );

        $labIndex = $normalized->search('laboratorium');

        $specColumns = [];
        foreach ($headings as $index => $heading) {
            if ($heading === '' || $index === $codeIndex) {
                continue;
            }

            if (in_array($normalized[$index], self::METADATA_COLUMNS, true)) {
                continue;
            }

            $specColumns[$index] = $heading;
        }

        foreach ($rows->values() as $offset => $row) {
            $excelRow = $offset + 2;

            try {
                $this->importRow($row, $codeIndex, $labIndex, $specColumns, $excelRow);
            } catch (\Throwable $e) {
                $this->errors[] = "Baris {$excelRow}: " . $e->getMessage();
            }
        }
    }

    private function importRow($row, int $codeIndex, $labIndex, array $specColumns, int $excelRow): void
    {
        $code = $this->cell($row, $codeIndex);

        if ($code === '') {
            $this->errors[] = "Baris {$excelRow}: kode komputer kosong.";

            return;
        }

        // Baris "Contoh" pada template dilewati agar tidak membuat data sampah.
        if (strcasecmp($code, 'Contoh') === 0) {
            return;
        }

        $computer = Computer::where('code', $code)->first();

        if (! $computer) {
            $computer = Computer::create(['code' => $code, 'status' => 'Aktif']);
            $this->createdComputersCount++;
        }

        if ($labIndex !== false && $labIndex !== null) {
            $labName = $this->cell($row, $labIndex);

            if ($labName !== '') {
                $lab = Laboratory::where('name', $labName)->first();

                if ($lab && $lab->id !== $computer->laboratory_id) {
                    $computer->update(['laboratory_id' => $lab->id]);
                }
            }
        }

        $hardwareIds = [];
        $hasSpecValue = false;

        foreach ($specColumns as $index => $category) {
            $value = $this->cell($row, $index);

            if ($value === '') {
                continue;
            }

            $hasSpecValue = true;

            foreach (preg_split('/[;\r\n]+/', $value) ?: [] as $part) {
                $name = trim($part);

                if ($name === '') {
                    continue;
                }

                $hardware = $this->resolveHardware($name, $category);
                $hardwareIds[$hardware->id] = $hardware->id;
            }
        }

        // Baris tanpa satu pun isi spesifikasi dibiarkan agar file yang belum
        // lengkap tidak menghapus data hardware yang sudah ada.
        if ($hasSpecValue) {
            $computer->hardware()->sync(array_values($hardwareIds));
        }

        $this->syncedCount++;
    }

    /**
     * Cari hardware berdasarkan nama + kategori (case-insensitive) supaya
     * komputer berbagai unit yang memakai komponen sama berbagi satu baris
     * katalog. Bila belum ada, buat baru dengan kode HW-xxx.
     */
    private function resolveHardware(string $name, string $category): Hardware
    {
        $existing = Hardware::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->whereRaw('LOWER(category) = ?', [mb_strtolower($category)])
            ->first();

        if ($existing) {
            return $existing;
        }

        $this->createdHardwareCount++;

        return Hardware::create([
            'code' => Hardware::generateCode(),
            'name' => mb_substr($name, 0, 255),
            'category' => mb_substr($category, 0, 255),
        ]);
    }

    private function cell($row, int $index): string
    {
        $value = $row instanceof Collection ? $row->get($index) : ($row[$index] ?? null);

        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    public function getSyncedCount(): int
    {
        return $this->syncedCount;
    }

    public function getCreatedComputersCount(): int
    {
        return $this->createdComputersCount;
    }

    public function getCreatedHardwareCount(): int
    {
        return $this->createdHardwareCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
