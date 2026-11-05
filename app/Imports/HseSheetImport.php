<?php

namespace App\Imports;

use App\Imports\Concerns\InteractsWithK3Rows;
use App\Models\HseInspection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/** Sheet "HSE": data utama inspeksi HSE. */
class HseSheetImport implements ToModel, WithHeadingRow
{
    use InteractsWithK3Rows;

    public function __construct(protected K3ImportContext $context) {}

    public function model(array $row)
    {
        if ($this->isBlankRow($row) || $this->isContohRow($row['kode'] ?? null)) {
            return null;
        }

        $rowNumber = $this->context->importedCount + count($this->context->errors) + 2;

        try {
            $tanggal = $this->parseDate($row['tanggal_inspeksi'] ?? null);
            if (! $tanggal) {
                $this->context->addError("Baris {$rowNumber}: Tanggal Inspeksi wajib diisi (format tanggal valid).");

                return null;
            }

            $required = [
                'lokasi' => $this->required($row['lokasi'] ?? null, 'Lokasi', $rowNumber),
                'diperiksa_oleh' => $this->required($row['diperiksa_oleh'] ?? null, 'Diperiksa Oleh', $rowNumber),
            ];
            if (in_array(null, $required, true)) {
                return null;
            }

            $attributes = [
                'lokasi' => $required['lokasi'],
                'diperiksa_oleh' => $required['diperiksa_oleh'],
                'tanggal_inspeksi' => $tanggal,
            ];

            $kode = $this->text($row['kode'] ?? null);

            if ($kode !== '') {
                $inspection = HseInspection::firstOrNew(['kode' => $kode]);
            } else {
                $inspection = new HseInspection;
            }

            $isNew = ! $inspection->exists;
            $inspection->fill($attributes);

            if ($isNew) {
                // Kolom findings NOT NULL; sheet "Temuan" pada file import
                // yang sama akan mengisinya sesudah sheet ini.
                $inspection->findings = [];
                $inspection->created_by = Auth::id();
            }

            $inspection->save();
            $this->context->importedCount++;

            return $inspection;
        } catch (\Throwable $e) {
            $this->context->addError("Baris {$rowNumber}: ".$e->getMessage());

            return null;
        }
    }

    private function required(mixed $value, string $field, int $rowNumber): ?string
    {
        $text = $this->text($value);
        if ($text === '') {
            $this->context->addError("Baris {$rowNumber}: {$field} wajib diisi.");

            return null;
        }

        return $text;
    }
}
