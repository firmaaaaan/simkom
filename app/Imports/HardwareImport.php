<?php

namespace App\Imports;

use App\Models\Hardware;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class HardwareImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $importedCount = 0;
    protected array $errors = [];

    public function model(array $row)
    {
        try {
            $code = !empty($row['kode']) ? $row['kode'] : Hardware::generateCode();

            $result = Hardware::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $row['nama'],
                    'brand' => $row['merk'] ?? null,
                    'model' => $row['model'] ?? null,
                    'category' => $row['kategori'],
                    'description' => $row['keterangan'] ?? null,
                ]
            );

            $this->importedCount++;
            return $result;
        } catch (\Exception $e) {
            $this->errors[] = 'Baris ' . ($this->importedCount + count($this->errors) + 1) . ': ' . $e->getMessage();
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'kode' => 'nullable|string|max:50',
            'nama' => 'required|string|max:255',
            'merk' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'kategori' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ];
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
