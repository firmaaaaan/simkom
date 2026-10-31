<?php

namespace App\Imports;

use App\Models\Computer;
use App\Models\Software;
use App\Services\SoftwareLicenseSync;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SoftwareLicenseSheetImport implements ToCollection, WithHeadingRow
{
    public function __construct(
        private SoftwareImportContext $context,
        private SoftwareLicenseSync $licenseSync,
    ) {
    }

    public function collection(Collection $rows): void
    {
        $grouped = [];

        foreach ($rows as $index => $row) {
            $code = trim((string) ($row['kode_software'] ?? ''));
            $key = trim((string) ($row['product_key'] ?? ''));
            $label = trim((string) ($row['label'] ?? ''));
            $pc = trim((string) ($row['pc'] ?? ''));

            if ($code === '' && $key === '' && $label === '' && $pc === '') {
                continue;
            }

            $grouped[$code][] = [
                'row' => $row,
                'line' => $index + 2,
            ];
        }

        foreach ($grouped as $code => $items) {
            if ($code === '') {
                $this->context->errors[] = 'Lisensi: Kode Software wajib diisi pada setiap baris.';

                continue;
            }

            $software = Software::where('code', $code)->first();

            if (! $software) {
                $this->context->errors[] = "Lisensi: Software dengan kode '{$code}' tidak ditemukan.";

                continue;
            }

            $this->syncSoftwareLicenses($software, $items);
        }
    }

    /**
     * @param  array<int, array{row: Collection, line: int}>  $items
     */
    private function syncSoftwareLicenses(Software $software, array $items): void
    {
        $existing = $software->licenses()->get();
        $oldComputerIds = $existing
            ->whereNotNull('assigned_computer_id')
            ->pluck('assigned_computer_id')
            ->unique()
            ->all();

        $matchedIds = [];
        $usedComputers = [];
        $processed = 0;

        foreach ($items as $item) {
            $row = $item['row'];
            $line = $item['line'];

            try {
                $key = trim((string) ($row['product_key'] ?? ''));
                $label = trim((string) ($row['label'] ?? ''));
                $pcCode = trim((string) ($row['pc'] ?? ''));
                $masked = $key !== '' && str_contains($key, '•');

                $computer = null;
                if ($pcCode !== '') {
                    $computer = Computer::where('code', $pcCode)->first();

                    if (! $computer) {
                        $this->context->errors[] = "Lisensi baris {$line}: PC '{$pcCode}' tidak ditemukan.";

                        continue;
                    }

                    if (isset($usedComputers[$computer->id])) {
                        $this->context->errors[] = "Lisensi baris {$line}: PC '{$pcCode}' dipakai lebih dari satu lisensi.";

                        continue;
                    }

                    $usedComputers[$computer->id] = true;
                }

                $purchase = $this->parseDate($row['tanggal_beli'] ?? null, $line, 'Tanggal Beli');
                $expiry = $this->parseDate($row['tanggal_expired'] ?? null, $line, 'Tanggal Expired');

                if ($purchase && $expiry && $expiry->lt($purchase)) {
                    $this->context->errors[] = "Lisensi baris {$line}: Tanggal Expired tidak boleh sebelum Tanggal Beli.";

                    continue;
                }

                $match = null;

                if (! $masked && $key !== '') {
                    $match = $existing->firstWhere('license_key', $key);
                }

                if (! $match && $label !== '') {
                    $match = $existing->firstWhere('label', $label);
                }

                if ($match && in_array($match->id, $matchedIds, true)) {
                    $match = null;
                }

                $attributes = [
                    'label' => $label !== '' ? $label : ($match?->label ?? 'Lisensi '.($processed + 1)),
                    'license_key' => (! $masked && $key !== '') ? $key : $match?->license_key,
                    'assigned_computer_id' => $computer?->id,
                    'purchase_date' => $purchase?->toDateString(),
                    'expiry_date' => $expiry?->toDateString(),
                    'notes' => trim((string) ($row['catatan'] ?? '')) ?: null,
                ];

                if ($match) {
                    $match->update($attributes);
                    $matchedIds[] = $match->id;
                } else {
                    $created = $software->licenses()->create($attributes);
                    $matchedIds[] = $created->id;
                }

                $processed++;
                $this->context->licenseCount++;
            } catch (\Throwable $e) {
                $this->context->errors[] = "Lisensi baris {$line}: ".$e->getMessage();
            }
        }

        if ($processed > 0) {
            $software->licenses()->whereNotIn('id', $matchedIds)->delete();
        }

        $this->licenseSync->syncComputers($software, $oldComputerIds);
    }

    private function parseDate(mixed $value, int $line, string $column): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(Date::excelToDateTimeObject((float) $value))->startOfDay();
            }

            return Carbon::parse((string) $value)->startOfDay();
        } catch (\Throwable) {
            throw new \InvalidArgumentException("{$column} tidak valid: {$value}");
        }
    }
}
