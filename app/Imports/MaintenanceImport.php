<?php

namespace App\Imports;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\MaintenanceChecklist;
use App\Models\MaintenanceChecklistItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Import pemeliharaan (matriks komputer × pertanyaan checklist) dari Excel.
 *
 * Format file:
 * - Baris judul dicari dengan mencari sel "Kode Komputer", sehingga blok judul
 *   di atasnya (Nama Lab, Periode, dst.) otomatis dilewati.
 * - Header didukung 1 baris (template); bila baris berikutnya membawa teks
 *   pertanyaan (mis. baris grup kategori di atas sub-kolom), baris itu yang
 *   dipakai sebagai judul kolom item.
 * - Kolom item dicocokkan persis per teks pertanyaan (case-insensitif) dari
 *   MaintenanceChecklist::getChecklistItems(). Kolom yang tidak ada di file
 *   → sel itu unchecked.
 * - Sel centang mendukung TRUE/1/✓/v/dsb., juga tulisan yang cocok dengan
 *   teks pertanyaannya.
 *
 * Perilaku:
 * - Konteks pemeliharaan (lab, tahun ajaran, tanggal, pemeriksa, catatan
 *   kategori) diambil dari form, bukan dari file.
 * - Kode komputer dicocokkan ke lab terpilih; tidak ada = error baris
 *   (tidak dibuat baru).
 * - Pemeliharaan lab + tahun ajaran + tanggal yang sama sudah ada → seluruh
 *   matriks itemnya ditimpa (idempoten).
 * - Baris "Contoh" (template) dilewati. Baris gagal dikumpulkan ke
 *   getErrors() tanpa menggagalkan seluruh file.
 */
class MaintenanceImport implements ToCollection
{
    /**
     * Nilai sel yang dianggap tercentang (case-insensitive).
     * Mencakup checkbox Excel (TRUE), hasil centang teks, dan Wingdings.
     */
    private const TRUTHY = [
        'true', '1', 'v', 'x', 'y', 'yes', 't', 'r', 'p', 'ya',
        'þ', 'ü', '√', '✓', '☑', '✔',
    ];

    private const NOTE_FIELDS = [
        'notes_computer',
        'notes_mouse_keyboard',
        'notes_ups',
        'notes_monitor',
    ];

    protected string $laboratoryId;

    protected ?AcademicYear $academicYear;

    protected string $maintenanceDate;

    protected ?string $inspectorName;

    protected array $notes;

    protected int $createdCount = 0;

    protected int $updatedCount = 0;

    protected array $errors = [];

    public function __construct(
        string $laboratoryId,
        string $academicYearId,
        string $maintenanceDate,
        ?string $inspectorName = null,
        array $notes = [],
    ) {
        $this->laboratoryId = $laboratoryId;
        $this->academicYear = AcademicYear::find($academicYearId);
        $this->maintenanceDate = $maintenanceDate;
        $this->inspectorName = $inspectorName;
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
        $columns = $this->buildColumns($rows, $headerRow, $headerIndex);

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
                    $matrix[$entry['computerId']] = $entry['checked'];
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
     * Susun pemetaan kolom: "Kode Komputer" + kolom tiap pertanyaan.
     *
     * Header 1 baris dicoba lebih dulu (template). Bila belum lengkap dan ada
     * baris kedua yang membawa lebih banyak teks pertanyaan (baris grup
     * kategori di atas sub-kolom), baris itu yang dipakai — data mulai dua
     * baris setelah judul. Bila tetap kosong, file ditolak.
     */
    private function buildColumns($rows, array $headerRow, int $headerIndex): ?array
    {
        $code = $this->findIndex($headerRow, fn ($cell) => preg_match('/^kode(\s+komputer)?$/i', trim((string) $cell)) === 1);

        if ($code === null) {
            $this->errors[] = 'Judul kolom "Kode Komputer" tidak ditemukan pada file.';

            return null;
        }

        $questions = $this->questionColumns();
        $items = $this->matchItemColumns($headerRow, $questions);
        $dataStart = $headerIndex + 1;

        if (count($items) < count($questions) && isset($rows[$headerIndex + 1])) {
            $second = $this->matchItemColumns($this->rowToArray($rows[$headerIndex + 1]), $questions);

            if (count($second) > count($items)) {
                $items = $second;
                $dataStart = $headerIndex + 2;
            }
        }

        if ($items === []) {
            $this->errors[] = 'Kolom pertanyaan pemeliharaan tidak ditemukan pada judul file.';

            return null;
        }

        return ['code' => $code, 'items' => $items, 'dataStart' => $dataStart];
    }

    /**
     * Peta kunci item "A.1"… "D.1" => teks pertanyaan lengkap.
     */
    private function questionColumns(): array
    {
        $columns = [];

        foreach (MaintenanceChecklist::getChecklistItems() as $category => $definition) {
            foreach ($definition['items'] as $index => $question) {
                $columns[$category.'.'.($index + 1)] = $question;
            }
        }

        return $columns;
    }

    /**
     * Cocokkan tiap pertanyaan ke satu kolom judul (label persis setelah
     * normalisasi). Satu kolom hanya boleh dipakai satu pertanyaan.
     */
    private function matchItemColumns(array $labels, array $questions): array
    {
        $normalized = array_map(fn ($cell) => $this->normalize((string) $cell), $labels);
        $claimed = [];
        $matches = [];

        foreach ($questions as $key => $question) {
            $needle = $this->normalize($question);

            foreach ($normalized as $index => $label) {
                if ($label !== '' && $label === $needle && ! isset($claimed[$index])) {
                    $claimed[$index] = true;
                    $matches[$key] = ['index' => $index];

                    break;
                }
            }
        }

        return $matches;
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

        $questions = $this->questionColumns();
        $checked = [];

        foreach ($columns['items'] as $key => $column) {
            $checked[$key] = $this->isTruthy($this->cell($row, $column['index']), $questions[$key]);
        }

        return ['computerId' => $computer->id, 'checked' => $checked];
    }

    /**
     * Tulis ulang matriks pemeliharaan: upsert record (lab + tahun ajaran +
     * tanggal), lalu satu baris database per komputer × pertanyaan — sama
     * seperti penyimpanan form (MaintenanceController::store).
     */
    private function persist(array $matrix): void
    {
        $attributes = [
            'laboratory_id' => $this->laboratoryId,
            'academic_year_id' => $this->academicYear?->id,
            'maintenance_date' => $this->maintenanceDate,
        ];

        $checklist = MaintenanceChecklist::where($attributes)->first();

        if ($checklist) {
            // Konteks dari form hanya menimpa bila diisi (catatan lama tidak
            // hilang hanya karena form kosong).
            $updates = [];

            if ($this->inspectorName !== null && $this->inspectorName !== '') {
                $updates['inspector_name'] = $this->inspectorName;
            }

            foreach (self::NOTE_FIELDS as $field) {
                $value = $this->notes[$field] ?? null;

                if (is_string($value) && $value !== '') {
                    $updates[$field] = $value;
                }
            }

            if ($updates !== []) {
                $checklist->update($updates);
            }

            $this->updatedCount++;
        } else {
            $checklist = MaintenanceChecklist::create(array_merge($attributes, [
                'inspector_name' => $this->inspectorName,
            ], array_intersect_key($this->notes, array_flip(self::NOTE_FIELDS))));

            $this->createdCount++;
        }

        $checklist->items()->delete();

        $computerIds = Computer::where('laboratory_id', $this->laboratoryId)->pluck('id');
        $now = now();
        $rows = [];

        foreach ($computerIds as $computerId) {
            foreach ($this->questionColumns() as $key => $question) {
                [$category, $number] = explode('.', $key);

                $rows[] = [
                    'id' => (string) Str::uuid7(),
                    'maintenance_checklist_id' => $checklist->id,
                    'computer_id' => $computerId,
                    'category' => $category,
                    'item_number' => (int) $number,
                    'is_checked' => (bool) ($matrix[$computerId][$key] ?? false),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            MaintenanceChecklistItem::insert($chunk);
        }
    }

    private function isTruthy(string $value, string $question): bool
    {
        $normalized = mb_strtolower(trim($value));

        if ($normalized === '') {
            return false;
        }

        // Tulisan yang cocok dengan teks pertanyaannya dianggap tercentang.
        if ($normalized === $this->normalize($question)) {
            return true;
        }

        return in_array($normalized, self::TRUTHY, true);
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
