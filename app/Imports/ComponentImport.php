<?php

namespace App\Imports;

use App\Models\Component;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ComponentImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $importedCount = 0;
    protected array $errors = [];

    public function model(array $row)
    {
        try {
            $code = !empty($row['kode']) ? $row['kode'] : Component::generateCode();

            $result = Component::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $row['nama'],
                    'category' => $row['kategori'],
                    'brand' => $row['merk'] ?? null,
                    'model' => $row['model'] ?? null,
                    'quantity' => $row['jumlah'] ?? 0,
                    'status' => $row['status'] ?? 'Tersedia',
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
            'kategori' => 'required|string|in:IoT,Jaringan,Lain-lain',
            'merk' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'jumlah' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:Tersedia,Digunakan,Rusak,Maintenance',
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
