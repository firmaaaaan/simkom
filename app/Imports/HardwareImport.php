<?php

namespace App\Imports;

use App\Models\Hardware;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class HardwareImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return Hardware::updateOrCreate(
            ['code' => $row['kode']],
            [
                'name' => $row['nama'],
                'brand' => $row['merk'] ?? null,
                'model' => $row['model'] ?? null,
                'category' => $row['kategori'],
                'quantity' => $row['jumlah'] ?? 0,
                'status' => $row['status'] ?? 'Tersedia',
                'description' => $row['keterangan'] ?? null,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'kode' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'merk' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'kategori' => 'required|string|max:255',
            'jumlah' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:Tersedia,Digunakan,Rusak,Maintenance',
            'keterangan' => 'nullable|string',
        ];
    }
}
