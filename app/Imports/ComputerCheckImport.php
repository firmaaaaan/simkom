<?php

namespace App\Imports;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\ComputerCheck;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Import kartu kendali (pengecekan) historis dari Excel.
 *
 * Format file:
 * - Baris judul dicari dengan mencari sel "Kode Komputer", sehingga blok judul
 *   di atasnya (Nama Lab, Periode, dst.) otomatis dilewati.
 * - Header boleh 1 baris (template) atau 2 baris (file asli dengan "Fungsi"
 *   yang di-merge di atas "Baik"/"Tidak").
 * - Kolom: Kode Komputer, Baik, Tidak, Keterangan (opsional), PJ (opsional).
 * - Sel centang mendukung TRUE/1/✓/v/dsb., juga tulisan "baik"/"tidak" yang
 *   cocok dengan nama kolomnya.
 *
 * Perilaku:
 * - Tanggal pengecekan diisi di form (semua baris memakai tanggal yang sama,
 *   disimpan sebagai created_at jam 08:00).
 * - Tahun ajaran ditentukan otomatis dari tanggal; bila belum ada, dibuat
 *   otomatis berstatus Non Aktif.
 * - "Baik" → status Baik; "Tidak" → status Perlu Perbaikan.
 * - Kode komputer yang tidak ada di sistem = error baris (tidak dibuat baru).
 * - Pengecekan komputer + tanggal yang sama sudah ada → diperbarui (idempoten).
 * - Nama PJ dicocokkan ke user; bila tidak ada, pengecek dikosongkan (tampil
 *   "System") dan namanya dilaporkan lewat getUnmatchedPjNames().
 * - Baris "Contoh" (template) dilewati. Baris gagal dikumpulkan ke getErrors()
 *   tanpa menggagalkan seluruh file.
 */
class ComputerCheckImport implements ToCollection
{
    /**
     * Nilai sel yang dianggap tercentang (case-insensitive).
     * Mencakup checkbox Excel (TRUE), hasil centang teks, dan Wingdings.
     */
    private const TRUTHY = [
        'true', '1', 'v', 'x', 'y', 'yes', 't', 'r', 'p',
        'þ', 'ü', '√', '✓', '☑', '✔',
    ];

    protected int $importedCount = 0;
    protected int $updatedCount = 0;
    protected array $errors = [];
    protected array $unmatchedPjNames = [];
    protected ?AcademicYear $academicYear = null;
    protected bool $academicYearCreated = false;
    protected Carbon $checkedAt;
    protected array $userCache = [];

    public function __construct(string $date)
    {
        $this->checkedAt = Carbon::parse($date)->setTime(8, 0);
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            $this->errors[] = 'File tidak berisi data.';

            return;
        }

        $headerIndex = null;
        foreach ($rows as $index => $row) {
            if ($this->findIndex($row, fn ($cell) => preg_match('/^kode(\s+komputer)?$/i', trim((string) $cell)) === 1) !== null) {
                $headerIndex = (int) $index;
                break;
            }
        }

        if ($headerIndex === null) {
            $this->errors[] = 'Judul kolom "Kode Komputer" tidak ditemukan pada file.';

            return;
        }

        $headerRow = $rows[$headerIndex];
        $subHeaderRow = $rows[$headerIndex + 1] ?? null;

        $columns = [
            'code' => $this->findIndex($headerRow, fn ($cell) => preg_match('/^kode(\s+komputer)?$/i', trim((string) $cell)) === 1),
        ];

        // "Baik" + "Tidak" harus berada di satu baris yang sama: baris judul
        // (template) atau baris keduanya (file dengan header "Fungsi" merge).
        $baik = $this->findLabel($headerRow, ['baik']);
        $tidak = $this->findLabel($headerRow, ['tidak']);

        if ($baik !== null && $tidak !== null) {
            $columns['baik'] = $baik;
            $columns['tidak'] = $tidak;
            $dataStart = $headerIndex + 1;
        } else {
            $baik = $subHeaderRow ? $this->findLabel($subHeaderRow, ['baik']) : null;
            $tidak = $subHeaderRow ? $this->findLabel($subHeaderRow, ['tidak']) : null;

            if ($baik === null || $tidak === null) {
                $this->errors[] = 'Kolom "Baik" dan "Tidak" tidak ditemukan pada judul file.';

                return;
            }

            $columns['baik'] = $baik;
            $columns['tidak'] = $tidak;
            $dataStart = $headerIndex + 2;
        }

        $columns['keterangan'] = $this->findLabel($headerRow, ['keterangan'])
            ?? ($subHeaderRow ? $this->findLabel($subHeaderRow, ['keterangan']) : null);
        $columns['pj'] = $this->findLabel($headerRow, ['pj', 'penanggung jawab'])
            ?? ($subHeaderRow ? $this->findLabel($subHeaderRow, ['pj', 'penanggung jawab']) : null);

        $this->academicYear = $this->resolveAcademicYear();

        foreach ($rows->slice($dataStart)->values() as $offset => $row) {
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $excelRow = $dataStart + $offset + 1;

            try {
                $this->importRow($row, $columns, $excelRow);
            } catch (\Throwable $e) {
                $this->errors[] = "Baris {$excelRow}: " . $e->getMessage();
            }
        }
    }

    private function importRow($row, array $columns, int $excelRow): void
    {
        $code = $this->cell($row, $columns['code']);

        if ($code === '') {
            $this->errors[] = "Baris {$excelRow}: kode komputer kosong.";

            return;
        }

        // Baris "Contoh" pada template dilewati agar tidak membuat data sampah.
        if (strcasecmp($code, 'Contoh') === 0) {
            return;
        }

        $baik = $this->isTruthy($this->cell($row, $columns['baik']), 'baik');
        $tidak = $this->isTruthy($this->cell($row, $columns['tidak']), 'tidak');

        if ($baik && $tidak) {
            $this->errors[] = "Baris {$excelRow}: kolom Baik dan Tidak sama-sama terisi.";

            return;
        }

        if (! $baik && ! $tidak) {
            $this->errors[] = "Baris {$excelRow}: kolom Baik/Tidak tidak dicentang.";

            return;
        }

        $computer = Computer::where('code', $code)->first();

        if (! $computer) {
            $this->errors[] = "Baris {$excelRow}: kode komputer \"{$code}\" tidak ditemukan di sistem.";

            return;
        }

        $notes = $columns['keterangan'] !== null ? $this->cell($row, $columns['keterangan']) : '';
        $pj = $columns['pj'] !== null ? $this->cell($row, $columns['pj']) : '';

        $attributes = [
            'overall_status' => $baik ? 'Baik' : 'Perlu Perbaikan',
            'notes' => $notes === '' ? null : $notes,
            'checked_by' => $this->resolveUserId($pj),
            'academic_year_id' => $this->academicYear?->id,
        ];

        // Idempoten: pengecekan pada komputer + tanggal sama diperbarui,
        // bukan ditambah ganda saat file diunggah ulang.
        $existing = ComputerCheck::where('computer_id', $computer->id)
            ->whereDate('created_at', $this->checkedAt->toDateString())
            ->first();

        if ($existing) {
            $existing->update($attributes);
            $this->updatedCount++;

            return;
        }

        // created_at tidak fillable, jadi dipaksa langsung untuk penanggalan
        // data historis (menentukan filter bulan/tahun di kartu kendali).
        ComputerCheck::forceCreate(array_merge($attributes, [
            'computer_id' => $computer->id,
            'created_at' => $this->checkedAt,
        ]));
        $this->importedCount++;
    }

    /**
     * Tentukan tahun ajaran dari tanggal pengecekan. Bila belum ada tahun yang
     * mencakup tanggal itu, buat otomatis (Non Aktif; periode default 1 Agustus
     * sampai 30 Juni).
     */
    private function resolveAcademicYear(): ?AcademicYear
    {
        $existing = AcademicYear::orderByDesc('start_year')
            ->get()
            ->first(fn (AcademicYear $year) => $year->coversDate($this->checkedAt));

        if ($existing) {
            return $existing;
        }

        if ($this->checkedAt->month >= 8) {
            $start = $this->checkedAt->year;
            $end = $this->checkedAt->year + 1;
        } else {
            $start = $this->checkedAt->year - 1;
            $end = $this->checkedAt->year;
        }

        $year = AcademicYear::firstOrCreate(
            ['start_year' => $start, 'end_year' => $end],
            ['name' => "Tahun Ajaran {$start}/{$end}", 'status' => 'Non Aktif']
        );
        $this->academicYearCreated = $year->wasRecentlyCreated;

        return $year;
    }

    /**
     * Cocokkan nama PJ ke user (case-insensitive). Nama tanpa pasangan
     * dicatat agar bisa dilaporkan di ringkasan impor.
     */
    private function resolveUserId(string $pj): ?string
    {
        if ($pj === '') {
            return null;
        }

        $key = mb_strtolower($pj);

        if (array_key_exists($key, $this->userCache)) {
            return $this->userCache[$key];
        }

        $user = User::whereRaw('LOWER(name) = ?', [$key])->first();

        if (! $user) {
            if (! in_array($pj, $this->unmatchedPjNames, true)) {
                $this->unmatchedPjNames[] = $pj;
            }

            return $this->userCache[$key] = null;
        }

        return $this->userCache[$key] = $user->id;
    }

    private function isTruthy(string $value, string $column): bool
    {
        $normalized = mb_strtolower(trim($value));

        if ($normalized === '') {
            return false;
        }

        // Tulisan eksplisit yang cocok dengan nama kolomnya: "baik" di kolom
        // Baik atau "tidak" di kolom Tidak dianggap tercentang.
        if ($normalized === mb_strtolower($column)) {
            return true;
        }

        return in_array($normalized, self::TRUTHY, true);
    }

    private function findLabel($row, array $labels): ?int
    {
        return $this->findIndex(
            $row,
            fn ($cell) => in_array(mb_strtolower(trim((string) $cell)), $labels, true)
        );
    }

    private function findIndex($row, callable $matcher): ?int
    {
        foreach ($row as $index => $value) {
            if ($matcher($value)) {
                return (int) $index;
            }
        }

        return null;
    }

    private function rowIsEmpty($row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function cell($row, ?int $index): string
    {
        if ($index === null) {
            return '';
        }

        $value = $row instanceof Collection ? $row->get($index) : ($row[$index] ?? null);

        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getUnmatchedPjNames(): array
    {
        return $this->unmatchedPjNames;
    }

    public function getAcademicYear(): ?AcademicYear
    {
        return $this->academicYear;
    }

    public function wasAcademicYearCreated(): bool
    {
        return $this->academicYearCreated;
    }
}
