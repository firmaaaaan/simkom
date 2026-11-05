<?php

namespace App\Imports;

use App\Imports\Concerns\InteractsWithK3Rows;
use App\Models\AccidentReport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AccidentReportImport implements ToModel, WithHeadingRow
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
            $tanggal = $this->parseDate($row['tanggal'] ?? null);
            if (! $tanggal) {
                $this->errors[] = "Baris {$rowNumber}: Tanggal wajib diisi (format tanggal valid).";

                return null;
            }

            $required = [
                'nama_pelapor' => $this->required($row['nama_pelapor'] ?? null, 'Nama Pelapor', $rowNumber),
                'jabatan_pelapor' => $this->required($row['jabatan_pelapor'] ?? null, 'Jabatan Pelapor', $rowNumber),
                'nama_korban' => $this->required($row['nama_korban'] ?? null, 'Nama Korban', $rowNumber),
                'alamat_korban' => $this->required($row['alamat_korban'] ?? null, 'Alamat Korban', $rowNumber),
                'ttl_korban' => $this->required($row['tempat_tanggal_lahir'] ?? null, 'Tempat Tanggal Lahir', $rowNumber),
                'unit_status_korban' => $this->required($row['unitstatus'] ?? $row['unit_status'] ?? null, 'Unit/Status', $rowNumber),
                'dampak' => $this->required($row['dampak'] ?? null, 'Dampak', $rowNumber),
                'saksi_satu' => $this->required($row['saksi_i'] ?? null, 'Saksi I', $rowNumber),
                'saksi_dua' => $this->required($row['saksi_ii'] ?? null, 'Saksi II', $rowNumber),
            ];
            if (in_array(null, $required, true)) {
                return null;
            }

            // Uraian kejadian dikumpulkan dari kolom Uraian Kejadian 1..5.
            $uraian = [];
            for ($i = 1; $i <= 5; $i++) {
                $text = $this->text($row['uraian_kejadian_'.$i] ?? null);
                if ($text !== '') {
                    $uraian[] = $text;
                }
            }

            if ($uraian === []) {
                $this->errors[] = "Baris {$rowNumber}: minimal satu Uraian Kejadian wajib diisi.";

                return null;
            }

            $attributes = [
                'tanggal' => $tanggal,
                'nama_pelapor' => $required['nama_pelapor'],
                'jabatan_pelapor' => $required['jabatan_pelapor'],
                'nama_korban' => $required['nama_korban'],
                'alamat_korban' => $required['alamat_korban'],
                'ttl_korban' => $required['ttl_korban'],
                'unit_status_korban' => $required['unit_status_korban'],
                'uraian_kejadian' => AccidentReport::normalizeUraian($uraian),
                'dampak' => $required['dampak'],
                'saksi_satu' => $required['saksi_satu'],
                'saksi_dua' => $required['saksi_dua'],
            ];

            $kode = $this->text($row['kode'] ?? null);

            if ($kode !== '') {
                $report = AccidentReport::firstOrNew(['kode' => $kode]);
            } else {
                $report = new AccidentReport;
            }

            $isNew = ! $report->exists;
            $report->fill($attributes);

            if ($isNew) {
                $report->created_by = Auth::id();
            }

            $report->save();
            $this->importedCount++;

            return $report;
        } catch (\Throwable $e) {
            $this->errors[] = "Baris {$rowNumber}: ".$e->getMessage();

            return null;
        }
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
