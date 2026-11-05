<?php

namespace App\Imports;

use App\Imports\Concerns\InteractsWithK3Rows;
use App\Models\AparInspection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AparInspectionImport implements ToModel, WithHeadingRow
{
    use InteractsWithK3Rows;

    protected int $importedCount = 0;

    protected array $errors = [];

    public function model(array $row)
    {
        if ($this->isBlankRow($row) || $this->isContohRow($row['kode'] ?? null)) {
            return null;
        }

        $rowNumber = $this->importedCount + count($this->errors) + 2;

        try {
            $tanggal = $this->parseDate($row['tanggal_inspeksi'] ?? null);
            if (! $tanggal) {
                $this->errors[] = "Baris {$rowNumber}: Tanggal Inspeksi wajib diisi (format tanggal valid).";

                return null;
            }

            $required = [
                'nomor_registrasi' => $this->required($row['no_registrasi'] ?? null, 'No. Registrasi', $rowNumber),
                'lokasi' => $this->required($row['lokasi'] ?? null, 'Lokasi', $rowNumber),
                'diperiksa_oleh' => $this->required($row['diperiksa_oleh'] ?? null, 'Diperiksa Oleh', $rowNumber),
            ];
            if (in_array(null, $required, true)) {
                return null;
            }

            // Status + keterangan tiap item: kolom "Kawat Segel" berisi
            // Baik/Tidak dan kolom "Ket. Kawat Segel" berisi keterangannya.
            $itemsInput = [];
            foreach (AparInspection::ITEMS as $key => $item) {
                $statusKey = $this->slug($item['label_id']);
                $ketKey = 'ket_'.$statusKey;

                $statusText = strtolower($this->text($row[$statusKey] ?? null));
                if ($statusText === '') {
                    $this->errors[] = "Baris {$rowNumber}: Item {$item['label_id']} wajib diisi Baik atau Tidak.";

                    return null;
                }

                $itemsInput[$key] = [
                    'status' => $statusText === 'tidak' ? 'tidak' : ($statusText === 'baik' ? 'baik' : null),
                    'keterangan' => $this->text($row[$ketKey] ?? null),
                ];

                if ($itemsInput[$key]['status'] === null) {
                    $this->errors[] = "Baris {$rowNumber}: Nilai item {$item['label_id']} harus Baik atau Tidak.";

                    return null;
                }
            }

            $attributes = [
                'nomor_registrasi' => $required['nomor_registrasi'],
                'tanggal_inspeksi' => $tanggal,
                'lokasi' => $required['lokasi'],
                'diperiksa_oleh' => $required['diperiksa_oleh'],
                'items' => AparInspection::normalizeItems($itemsInput),
            ];

            $kode = $this->text($row['kode'] ?? null);

            if ($kode !== '') {
                $inspection = AparInspection::firstOrNew(['kode' => $kode]);
            } else {
                $inspection = new AparInspection;
            }

            $isNew = ! $inspection->exists;
            $inspection->fill($attributes);

            if ($isNew) {
                $inspection->created_by = Auth::id();
            }

            $inspection->save();
            $this->importedCount++;

            return $inspection;
        } catch (\Throwable $e) {
            $this->errors[] = "Baris {$rowNumber}: ".$e->getMessage();

            return null;
        }
    }

    /** Label item menjadi key heading, mis. "Kawat Segel" => kawat_segel. */
    private function slug(string $label): string
    {
        return str_replace([' ', '-', '.'], '_', strtolower($label));
    }

    private function required(mixed $value, string $field, int $rowNumber): ?string
    {
        $text = $this->text($value);
        if ($text === '') {
            $this->errors[] = "Baris {$rowNumber}: {$field} wajib diisi.";

            return null;
        }

        return $text;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
