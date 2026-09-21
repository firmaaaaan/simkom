<?php

namespace App\Imports;

use App\Models\Component;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ComponentImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return Component::updateOrCreate(
            ['code' => $row['kode']],
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
    }

    public function rules(): array
    {
        return [
            'kode' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string|in:IoT,Jaringan,Lain-lain',
            'merk' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'jumlah' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:Tersedia,Digunakan,Rusak,Maintenance',
            'keterangan' => 'nullable|string',
        ];
    }
}
