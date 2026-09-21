<?php

namespace App\Imports;

use App\Models\Software;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SoftwareImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $importedCount = 0;
    protected array $errors = [];

    public function model(array $row)
    {
        try {
            $code = !empty($row['kode']) ? $row['kode'] : Software::generateCode();

            $result = Software::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $row['nama'],
                    'version' => $row['versi'] ?? null,
                    'category' => $row['kategori'],
                    'license_type' => $row['jenis_lisensi'] ?? null,
                    'status' => $row['status'] ?? 'Aktif',
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
            'versi' => 'nullable|string|max:50',
            'kategori' => 'required|string|max:255',
            'jenis_lisensi' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:Aktif,Expired,Trial,Non Aktif',
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
