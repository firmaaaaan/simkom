<?php

namespace App\Imports;

use App\Imports\Concerns\InteractsWithK3Rows;
use App\Models\K3InspectionReport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/** Sheet "Laporan": data utama laporan inspeksi K3 (Form 05). */
class K3InspectionSheetImport implements ToModel, WithHeadingRow
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
            $tanggalBa = $this->parseDate($row['tanggal_ba'] ?? null);
            if (! $tanggalBa) {
                $this->context->addError("Baris {$rowNumber}: Tanggal BA wajib diisi (format tanggal valid).");

                return null;
            }

            $required = [
                'bulan_ba' => $this->required($row['bulan_ba'] ?? null, 'Bulan BA', $rowNumber),
                'tahun_ba' => $this->required($row['tahun_ba'] ?? null, 'Tahun BA', $rowNumber),
                'bulan_inspeksi' => $this->required($row['bulan_inspeksi'] ?? null, 'Bulan Inspeksi', $rowNumber),
                'lokasi' => $this->required($row['lokasi'] ?? null, 'Lokasi', $rowNumber),
                'pemeriksa_nama' => $this->required($row['pemeriksa_nama'] ?? null, 'Pemeriksa Nama', $rowNumber),
                'pemeriksa_jabatan' => $this->required($row['pemeriksa_jabatan'] ?? null, 'Pemeriksa Jabatan', $rowNumber),
            ];
            if (in_array(null, $required, true)) {
                return null;
            }

            $attributes = [
                'hari' => $this->text($row['hari'] ?? null) ?: null,
                'tanggal_ba' => $tanggalBa,
                'bulan_ba' => $required['bulan_ba'],
                'tahun_ba' => $required['tahun_ba'],
                'bulan_inspeksi' => $required['bulan_inspeksi'],
                'lokasi' => $required['lokasi'],
                'pemeriksa_nama' => $required['pemeriksa_nama'],
                'pemeriksa_jabatan' => $required['pemeriksa_jabatan'],
            ];

            $kode = $this->text($row['kode'] ?? null);

            if ($kode !== '') {
                $report = K3InspectionReport::firstOrNew(['kode' => $kode]);
            } else {
                $report = new K3InspectionReport;
            }

            $isNew = ! $report->exists;
            $report->fill($attributes);

            if ($isNew) {
                // Checklist default dibuat saat record baru; dapat ditimpa
                // lewat sheet Checklist pada file import yang sama. Kolom
                // findings NOT NULL diisi sheet "Temuan" berikutnya.
                $report->checklist_items = K3InspectionReport::defaultChecklist();
                $report->findings = [];
                $report->created_by = Auth::id();
            }

            $report->save();
            $this->context->importedCount++;

            return $report;
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
