<?php

namespace App\Imports;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\DeviceCheck;
use App\Models\DeviceCheckItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Import pengecekan perangkat (matriks komputer × item) dari Excel.
 *
 * Format file:
 * - Baris judul dicari dengan mencari sel "Kode Komputer", sehingga blok judul
 *   di atasnya (Nama Lab, Periode, dst.) otomatis dilewati.
 * - Header didukung 1 baris (template) maupun 2 baris (salinan hasil cetak
 *   dengan grup "Kabel Power" yang di-merge di atas PC/CPU/UPS).
 * - Kolom item cocok per label (case-insensitif), termasuk label bergrup
 *   "Kabel Power PC". Kolom item yang tidak ada di file → sel itu tidak_ada.
 * - Tiap sel berisi status: ✓/TRUE/1/dsb. = berfungsi, "rusak"/✗ = rusak,
 *   "–"/"tidak ada" = tidak_ada. Sel kosong dianggap tidak_ada (mempertahankan
 *   arti sel kosong pada file lama yang berarti perangkat bermasalah/tidak ada).
 *
 * Perilaku:
 * - Konteks pengecekan (lab, tahun ajaran, tanggal, petugas, catatan) diambil
 *   dari form, bukan dari file.
 * - Kode komputer dicocokkan ke lab terpilih; tidak ada = error baris
 *   (tidak dibuat baru).
 * - Pengecekan lab + tahun ajaran + tanggal yang sama sudah ada → seluruh
 *   matriks itemnya ditimpa (idempoten).
 * - Baris "Contoh" (template) dilewati. Baris gagal dikumpulkan ke
 *   getErrors() tanpa menggagalkan seluruh file.
 */
class DeviceCheckImport implements ToCollection
{
    /**
     * Nilai sel yang dianggap berfungsi (case-insensitive). Mencakup checkbox
     * Excel (TRUE), hasil centang teks, dan Wingdings.
     */
    private const BERFUNGSI = [
        'true', '1', 'v', 'x', 'y', 'yes', 't', 'p', 'ya',
        'þ', 'ü', '√', '✓', '☑', '✔', 'berfungsi', 'baik',
    ];

    /** Nilai sel yang dianggap rusak — "r" sengaja dipakai sebagai singkatan. */
    private const RUSAK = [
        'r', 'rusak', 'broken', '✗', '×',
    ];

    /** Nilai sel yang dianggap tidak ada/tidak terpasang. */
    private const TIDAK_ADA = [
        'ta', 'tdk ada', 'tidak ada', 'tidakada', 'tidak-ada', 'hilang',
        'n/a', 'na', '–', '-', '—',
    ];

    protected string $laboratoryId;

    protected ?AcademicYear $academicYear;

    protected string $checkDate;

    protected ?string $officerName;

    protected ?string $notes;

    protected int $createdCount = 0;

    protected int $updatedCount = 0;

    protected array $errors = [];

    public function __construct(
        string $laboratoryId,
        string $academicYearId,
        string $checkDate,
        ?string $officerName = null,
        ?string $notes = null,
    ) {
        $this->laboratoryId = $laboratoryId;
        $this->academicYear = AcademicYear::find($academicYearId);
        $this->checkDate = $checkDate;
        $this->officerName = $officerName;
        $this->notes = $notes;
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

        $headerRow = $this->rowToArray($rows[$headerIndex]);
        $subHeaderRow = isset($rows[$headerIndex + 1]) ? $this->rowToArray($rows[$headerIndex + 1]) : null;

        $columns = $this->buildColumns($headerRow, $subHeaderRow, $headerIndex);

        if ($columns === null) {
            return;
        }

        $matrix = [];

        foreach ($rows->slice($columns['dataStart'])->values() as $offset => $row) {
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $excelRow = $columns['dataStart'] + $offset + 1;

            try {
                $entry = $this->parseRow($row, $columns, $excelRow);

                if ($entry !== null) {
                    $matrix[$entry['computerId']] = $entry['statuses'];
                }
            } catch (\Throwable $e) {
                $this->errors[] = "Baris {$excelRow}: ".$e->getMessage();
            }
        }

        if ($matrix === []) {
            $this->errors[] = 'Tidak ada baris data yang valid.';

            return;
        }

        $this->persist($matrix);
    }

    /**
     * Susun pemetaan kolom: "Kode Komputer" + kolom tiap item.
     *
     * Header 1 baris dicoba lebih dulu (template). Bila belum lengkap dan ada
     * baris kedua, baris judul digabung (sel merge "Kabel Power" + sub-kolom)
     * lalu dicocokkan ulang — meniru format hasil cetak. Bila tetap tidak
     * lengkap, pakai hasil pencocokan sebagian (kolom hilang = tidak_ada).
     */
    private function buildColumns(array $headerRow, ?array $subHeaderRow, int $headerIndex): ?array
    {
        $code = $this->findIndex($headerRow, fn ($cell) => preg_match('/^kode(\s+komputer)?$/i', trim((string) $cell)) === 1);

        if ($code === null) {
            $this->errors[] = 'Judul kolom "Kode Komputer" tidak ditemukan pada file.';

            return null;
        }

        $itemColumns = DeviceCheck::itemColumns();
        $items = $this->matchItemColumns($headerRow);
        $dataStart = $headerIndex + 1;

        if (count($items) < count($itemColumns) && $subHeaderRow !== null) {
            $joined = $this->joinHeaderRows($headerRow, $subHeaderRow);
            $twoRow = $this->matchItemColumns($joined);

            if (count($twoRow) === count($itemColumns)) {
                $items = $twoRow;
                $dataStart = $headerIndex + 2;
            }
        }

        if ($items === []) {
            $this->errors[] = 'Kolom item perangkat tidak ditemukan pada judul file.';

            return null;
        }

        return ['code' => $code, 'items' => $items, 'dataStart' => $dataStart];
    }

    /**
     * Cocokkan tiap item ke satu kolom judul (label persis setelah normalisasi).
     * Label bergrup dicoba lebih dulu ("Kabel Power UPS" sebelum "UPS") agar
     * kolom UPS tunggal tidak diklaim oleh item kabel power.
     */
    private function matchItemColumns(array $labels): array
    {
        $normalized = array_map(fn ($cell) => $this->normalize((string) $cell), $labels);
        $claimed = [];
        $matches = [];

        foreach (DeviceCheck::itemColumns() as $column) {
            $plain = $this->normalize($column['label']);
            $aliases = isset($column['group'])
                ? [$this->normalize($column['group'].' '.$column['label']), $plain]
                : [$plain];

            foreach ($aliases as $alias) {
                foreach ($normalized as $index => $label) {
                    if ($label !== '' && $label === $alias && ! isset($claimed[$index])) {
                        $claimed[$index] = true;
                        $matches[$column['key']] = ['index' => $index, 'label' => $alias];

                        continue 3;
                    }
                }
            }
        }

        return $matches;
    }

    /**
     * Gabungkan dua baris judul: sel merge di baris atas membawa nama grup ke
     * sub-kolomnya ("Kabel Power" + "CPU" → "Kabel Power CPU").
     */
    private function joinHeaderRows(array $top, array $bottom): array
    {
        $result = [];
        $group = '';

        foreach ($top as $index => $topCell) {
            $topLabel = trim((string) $topCell);
            $bottomLabel = trim((string) ($bottom[$index] ?? ''));

            if ($topLabel !== '') {
                $group = $topLabel;
            }

            if ($topLabel !== '' && $bottomLabel !== '') {
                $result[$index] = $topLabel.' '.$bottomLabel;
            } elseif ($topLabel !== '') {
                $result[$index] = $topLabel;
            } else {
                $result[$index] = ($bottomLabel !== '' && $group !== '')
                    ? $group.' '.$bottomLabel
                    : $bottomLabel;
            }
        }

        return $result;
    }

    private function parseRow($row, array $columns, int $excelRow): ?array
    {
        $code = $this->cell($row, $columns['code']);

        if ($code === '') {
            $this->errors[] = "Baris {$excelRow}: kode komputer kosong.";

            return null;
        }

        // Baris "Contoh" pada template dilewati agar tidak membuat data sampah.
        if (strcasecmp($code, 'Contoh') === 0) {
            return null;
        }

        $computer = Computer::where('code', $code)
            ->where('laboratory_id', $this->laboratoryId)
            ->first();

        if (! $computer) {
            $this->errors[] = "Baris {$excelRow}: kode komputer \"{$code}\" tidak ditemukan di lab ini.";

            return null;
        }

        $statuses = [];

        foreach ($columns['items'] as $itemKey => $column) {
            $statuses[$itemKey] = $this->parseStatus($this->cell($row, $column['index']), $column['label']);
        }

        return ['computerId' => $computer->id, 'statuses' => $statuses];
    }

    /**
     * Tulis ulang matriks pengecekan: upsert record pengecekan (lab + tahun
     * ajaran + tanggal), lalu satu baris database per komputer × item — sama
     * seperti penyimpanan form edit (DeviceCheckController::syncItems).
     */
    private function persist(array $matrix): void
    {
        $attributes = [
            'laboratory_id' => $this->laboratoryId,
            'academic_year_id' => $this->academicYear?->id,
            'check_date' => $this->checkDate,
        ];

        $check = DeviceCheck::where($attributes)->first();

        if ($check) {
            // Konteks dari form hanya menimpa bila diisi (catatan lama tidak
            // hilang hanya karena form kosong).
            $updates = [];

            if ($this->officerName !== null && $this->officerName !== '') {
                $updates['officer_name'] = $this->officerName;
            }

            if ($this->notes !== null && $this->notes !== '') {
                $updates['notes'] = $this->notes;
            }

            if ($updates !== []) {
                $check->update($updates);
            }

            $this->updatedCount++;
        } else {
            $check = DeviceCheck::create(array_merge($attributes, [
                'officer_name' => $this->officerName,
                'notes' => $this->notes,
            ]));
            $this->createdCount++;
        }

        $check->items()->delete();

        $computerIds = Computer::where('laboratory_id', $this->laboratoryId)->pluck('id');
        $itemKeys = DeviceCheck::itemKeys();
        $now = now();
        $rows = [];

        foreach ($computerIds as $computerId) {
            foreach ($itemKeys as $itemKey) {
                $rows[] = [
                    'id' => (string) Str::uuid7(),
                    'device_check_id' => $check->id,
                    'computer_id' => $computerId,
                    'item_key' => $itemKey,
                    // Komputer yang tidak tercantum pada file dianggap tidak_ada,
                    // konsisten dengan sel kosong (arti lama: bermasalah/tidak ada).
                    'status' => $matrix[$computerId][$itemKey] ?? DeviceCheckItem::STATUS_TIDAK_ADA,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DeviceCheckItem::insert($chunk);
        }
    }

    /**
     * Terjemahkan isi sel Excel menjadi status pengecekan.
     * Urutan: kosong → tidak_ada; lalu token rusak; token tidak ada; nama
     * kolom item; token berfungsi; sisanya tidak_ada.
     */
    private function parseStatus(string $value, string $column): string
    {
        $normalized = mb_strtolower(trim($value));

        if ($normalized === '') {
            return DeviceCheckItem::STATUS_TIDAK_ADA;
        }

        if (in_array($normalized, self::RUSAK, true)) {
            return DeviceCheckItem::STATUS_RUSAK;
        }

        if (in_array($normalized, self::TIDAK_ADA, true)) {
            return DeviceCheckItem::STATUS_TIDAK_ADA;
        }

        // Tulisan yang cocok dengan nama kolom itemnya dianggap berfungsi.
        if ($normalized === $column) {
            return DeviceCheckItem::STATUS_BERFUNGSI;
        }

        if (in_array($normalized, self::BERFUNGSI, true)) {
            return DeviceCheckItem::STATUS_BERFUNGSI;
        }

        return DeviceCheckItem::STATUS_TIDAK_ADA;
    }

    private function normalize(string $value): string
    {
        return preg_replace('/\s+/', ' ', mb_strtolower(trim($value)));
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

    /**
     * Baris dari reader bisa berupa Collection atau array — samakan dulu.
     */
    private function rowToArray($row): array
    {
        return $row instanceof Collection ? $row->all() : (array) $row;
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

    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getAcademicYear(): ?AcademicYear
    {
        return $this->academicYear;
    }
}
