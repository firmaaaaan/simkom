<?php

namespace App\Imports;

use App\Models\Software;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SoftwareSheetImport implements ToModel, WithHeadingRow, WithValidation
{
    public function __construct(private SoftwareImportContext $context)
    {
    }

    public function model(array $row)
    {
        try {
            $code = ! empty($row['kode']) ? trim((string) $row['kode']) : Software::generateCode();

            $software = Software::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $row['nama'],
                    'version' => $row['versi'] ?? null,
                    'category' => $row['kategori'],
                    'license_type' => $row['jenis_lisensi'] ?? null,
                    'pic_name' => $row['penanggung_jawab'] ?? null,
                    'pic_email' => $row['email_pj'] ?? null,
                    'status' => $row['status'] ?? 'Aktif',
                    'description' => $row['keterangan'] ?? null,
                    'provider' => $row['penyedia'] ?? null,
                    'download_link' => $row['link_download'] ?? null,
                ]
            );

            $this->context->softwareCount++;

            return $software;
        } catch (\Throwable $e) {
            $this->context->errors[] = 'Software: '.$e->getMessage();

            return null;
        }
    }

    /**
     * Excel menyimpan sel angka (mis. Versi "2024") sebagai number, dan sel
     * kosong sebagai string kosong. Nilai dinormalkan dulu supaya rule string
     * dan nullable tidak menolak baris yang sah.
     */
    public function prepareForValidation(array $row, int $rowIndex): array
    {
        foreach ($row as $key => $value) {
            if ($value === null || is_bool($value)) {
                continue;
            }

            $value = trim((string) $value);
            $value = $value === '' ? null : $value;

            // Link tanpa skema (mis. drive.google.com/...) dilengkapi https://
            // supaya lolos rule url dan tetap bisa diklik saat ditampilkan.
            if ($key === 'link_download' && $value !== null && ! preg_match('#^https?://#i', $value)) {
                $value = 'https://' . $value;
            }

            $row[$key] = $value;
        }

        return $row;
    }

    public function rules(): array
    {
        return [
            'kode' => 'nullable|string|max:50',
            'nama' => 'required|string|max:255',
            'versi' => 'nullable|string|max:50',
            'kategori' => 'required|string|max:255',
            'jenis_lisensi' => 'nullable|string|max:100',
            'penanggung_jawab' => 'nullable|string|max:255',
            'email_pj' => 'nullable|email|max:255',
            'status' => 'nullable|string|in:Aktif,Expired,Trial,Non Aktif',
            'keterangan' => 'nullable|string',
            'penyedia' => 'nullable|string|max:255',
            'link_download' => 'nullable|url|max:255',
        ];
    }
}
