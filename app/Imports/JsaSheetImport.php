<?php

namespace App\Imports;

use App\Imports\Concerns\InteractsWithK3Rows;
use App\Models\JobSafetyAnalysis;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/** Sheet "JSA": data utama analisis keselamatan pekerjaan. */
class JsaSheetImport implements ToModel, WithHeadingRow
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
            $tanggal = $this->parseDate($row['tanggal_pekerjaan'] ?? null);
            if (! $tanggal) {
                $this->context->addError("Baris {$rowNumber}: Tanggal Pekerjaan wajib diisi (format tanggal valid).");

                return null;
            }

            $required = [
                'nama_pekerja' => $this->required($row['nama_pekerja'] ?? null, 'Nama Pekerja', $rowNumber),
                'nama_pekerjaan' => $this->required($row['nama_pekerjaan'] ?? null, 'Nama Pekerjaan', $rowNumber),
                'tempat' => $this->required($row['tempat'] ?? null, 'Tempat', $rowNumber),
            ];
            if (in_array(null, $required, true)) {
                return null;
            }

            $attributes = [
                'nama_pekerja' => $required['nama_pekerja'],
                'nama_pekerjaan' => $required['nama_pekerjaan'],
                'tanggal_pekerjaan' => $tanggal,
                'tempat' => $required['tempat'],
                'apd' => $this->parseApd($row['apd'] ?? $row['apd_dipisah_koma'] ?? null),
                'apd_lain' => $this->text($row['apd_lain'] ?? null) ?: null,
            ];

            $kode = $this->text($row['kode'] ?? null);

            if ($kode !== '') {
                $jsa = JobSafetyAnalysis::firstOrNew(['kode' => $kode]);
            } else {
                $jsa = new JobSafetyAnalysis;
            }

            $isNew = ! $jsa->exists;
            $jsa->fill($attributes);

            if ($isNew) {
                // Kolom steps NOT NULL; sheet "Langkah Pekerjaan" pada file
                // import yang sama akan mengisinya sesudah sheet ini.
                $jsa->steps = [];
                $jsa->created_by = Auth::id();
            }

            $jsa->save();
            $this->context->importedCount++;

            return $jsa;
        } catch (\Throwable $e) {
            $this->context->addError("Baris {$rowNumber}: ".$e->getMessage());

            return null;
        }
    }

    /** Sel APD berisi daftar label dipisah koma, mis. "Helm, Sarung Tangan". */
    private function parseApd(mixed $value): array
    {
        $text = $this->text($value);
        if ($text === '') {
            return [];
        }

        $labels = array_map(fn ($item) => mb_strtolower(trim($item)), explode(',', $text));

        $apd = [];
        foreach (JobSafetyAnalysis::APD_ITEMS as $key => $item) {
            if (in_array(mb_strtolower($item['label_id']), $labels, true)) {
                $apd[$key] = true;
            }
        }

        $known = collect(JobSafetyAnalysis::APD_ITEMS)->pluck('label_id')->map(fn ($label) => mb_strtolower($label))->all();
        foreach ($labels as $label) {
            if ($label !== '' && ! in_array($label, $known, true)) {
                $this->context->addError('APD "'.$label.'" tidak dikenal (dilewati). Item yang dikenal: '.collect(JobSafetyAnalysis::APD_ITEMS)->pluck('label_id')->implode(', ').'.');
            }
        }

        return $apd;
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
