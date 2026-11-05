<?php

namespace App\Imports;

use App\Imports\Concerns\InteractsWithK3Rows;
use App\Models\IncidentReport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class IncidentReportImport implements ToModel, WithHeadingRow
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
            $tanggal = $this->parseDate($row['tanggal_kejadian'] ?? null);
            $waktu = $this->parseTime($row['waktu_kejadian'] ?? null);

            if (! $tanggal) {
                $this->errors[] = "Baris {$rowNumber}: Tanggal Kejadian wajib diisi (format tanggal valid).";

                return null;
            }

            if (! $waktu) {
                $this->errors[] = "Baris {$rowNumber}: Waktu Kejadian wajib diisi (format HH:MM).";

                return null;
            }

            $jenis = $this->resolveJenis($row['jenis_kejadian'] ?? null);
            if (! $jenis) {
                $this->errors[] = "Baris {$rowNumber}: Jenis Kejadian harus salah satu dari Kecelakaan, Insiden, Nearmiss, atau Duga Bahaya.";

                return null;
            }

            // Terima heading "Likelihood" maupun "Likelihood (1-5)".
            $likelihood = (int) ($row['likelihood'] ?? $row['likelihood_1_5'] ?? 0);
            $impact = (int) ($row['impact'] ?? $row['impact_1_5'] ?? 0);
            if ($likelihood < 1 || $likelihood > 5 || $impact < 1 || $impact > 5) {
                $this->errors[] = "Baris {$rowNumber}: Likelihood dan Impact harus angka 1-5.";

                return null;
            }

            $attributes = [
                'tanggal_kejadian' => $tanggal,
                'waktu_kejadian' => $waktu,
                'telah_terjadi' => $jenis,
                'lokasi' => $this->required($row['lokasi'] ?? null, 'Lokasi', $rowNumber),
                'nama_korban' => $this->required($row['nama_korban'] ?? null, 'Nama Korban', $rowNumber),
                'jabatan' => $this->text($row['jabatan'] ?? null) ?: null,
                'asal_institusi' => $this->text($row['asal_institusi'] ?? null) ?: null,
                'sebab' => $this->text($row['sebab'] ?? null) ?: null,
                'riwayat' => $this->text($row['riwayat'] ?? null) ?: null,
                'saksi' => $this->text($row['saksi'] ?? null) ?: null,
                'akibat' => $this->text($row['akibat'] ?? null) ?: null,
                'likelihood' => $likelihood,
                'impact' => $impact,
                'pelapor' => $this->required($row['pelapor'] ?? null, 'Pelapor', $rowNumber),
            ];

            if (in_array(null, $attributes, true)) {
                return null;
            }

            $kode = $this->text($row['kode'] ?? null);

            if ($kode !== '') {
                $report = IncidentReport::firstOrNew(['kode' => $kode]);
            } else {
                $report = new IncidentReport;
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

    /** Terima label maupun key jenis kejadian, mis. "Nearmiss (Nyaris celaka)". */
    private function resolveJenis(mixed $value): ?string
    {
        $text = $this->text($value);
        if ($text === '') {
            return null;
        }

        foreach (IncidentReport::JENIS_KEJADIAN as $key => $label) {
            if (strcasecmp($text, $key) === 0 || strcasecmp($text, $label) === 0) {
                return $key;
            }
        }

        return null;
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
