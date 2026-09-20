<?php

namespace App\Imports;

use App\Models\Software;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SoftwareImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return Software::updateOrCreate(
            ['code' => $row['kode']],
            [
                'name' => $row['nama'],
                'version' => $row['versi'] ?? null,
                'category' => $row['kategori'],
                'license_type' => $row['jenis_lisensi'] ?? null,
                'license_count' => $row['jumlah_lisensi'] ?? 0,
                'status' => $row['status'] ?? 'Aktif',
                'description' => $row['keterangan'] ?? null,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'kode' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'versi' => 'nullable|string|max:50',
            'kategori' => 'required|string|max:255',
            'jenis_lisensi' => 'nullable|string|max:100',
            'jumlah_lisensi' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:Aktif,Expired,Trial,Non Aktif',
            'keterangan' => 'nullable|string',
        ];
    }
}
