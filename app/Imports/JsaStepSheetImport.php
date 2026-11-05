<?php

namespace App\Imports;

use App\Imports\Concerns\InteractsWithK3Rows;
use App\Models\JobSafetyAnalysis;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Sheet "Langkah Pekerjaan": baris langkah dikelompokkan berdasarkan
 * Kode JSA, lalu mengganti seluruh langkah pekerjaan JSA terkait.
 */
class JsaStepSheetImport implements ToCollection, WithHeadingRow
{
    use InteractsWithK3Rows;

    public function __construct(protected K3ImportContext $context) {}

    public function collection(Collection $rows): void
    {
        $grouped = [];

        foreach ($rows as $index => $row) {
            $rowArray = $this->toRowArray($row);

            if ($this->isBlankRow($rowArray)) {
                continue;
            }

            $kode = $this->text($rowArray['kode_jsa'] ?? null);
            if ($kode === '' || $this->isContohRow($kode)) {
                continue;
            }

            $step = [
                'langkah' => $this->text($rowArray['urutan_langkah_pekerjaan'] ?? null),
                'bahaya' => $this->text($rowArray['identifikasi_bahaya'] ?? null),
                'pengendalian' => $this->text($rowArray['pengendalian'] ?? null),
                'penanggung_jawab' => $this->text($rowArray['penanggung_jawab'] ?? null),
            ];

            if ($step['langkah'] === '') {
                $this->context->addError('Langkah Pekerjaan baris '.($index + 2).': Urutan Langkah Pekerjaan wajib diisi.');

                continue;
            }

            $grouped[$kode][] = $step;
        }

        foreach ($grouped as $kode => $steps) {
            $jsa = JobSafetyAnalysis::where('kode', $kode)->first();

            if (! $jsa) {
                $this->context->addError("Langkah Pekerjaan: Kode JSA \"{$kode}\" tidak ditemukan (pastikan sheet JSA memuat kode tersebut).");

                continue;
            }

            $jsa->steps = JobSafetyAnalysis::normalizeSteps($steps);
            $jsa->save();
        }
    }
}
