<?php

namespace App\Imports;

use App\Imports\Concerns\InteractsWithK3Rows;
use App\Models\HseInspection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Sheet "Temuan": baris temuan dikelompokkan berdasarkan Kode HSE,
 * lalu mengganti seluruh temuan inspeksi HSE terkait.
 */
class HseFindingSheetImport implements ToCollection, WithHeadingRow
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

            $kode = $this->text($rowArray['kode_hse'] ?? null);
            if ($kode === '' || $this->isContohRow($kode)) {
                continue;
            }

            $finding = [
                'lokasi' => $this->text($rowArray['lokasi'] ?? null),
                'temuan' => $this->text($rowArray['temuan'] ?? null),
                'unsafe_condition' => $this->parseYesNo($rowArray['unsafe_condition'] ?? $rowArray['unsafe_condition_ya_tidak'] ?? $rowArray['unsafe_condition_yatidak'] ?? null),
                'unsafe_action' => $this->parseYesNo($rowArray['unsafe_action'] ?? $rowArray['unsafe_action_ya_tidak'] ?? $rowArray['unsafe_action_yatidak'] ?? null),
                'tanggal_kejadian' => $this->parseDate($rowArray['tanggal_kejadian'] ?? null),
                'tindakan_perbaikan' => $this->text($rowArray['tindakan_perbaikan'] ?? null),
                'tanggal_perbaikan' => $this->parseDate($rowArray['tanggal_perbaikan'] ?? null),
                'pic' => $this->text($rowArray['pic'] ?? null),
            ];

            $grouped[$kode][] = $finding;
        }

        foreach ($grouped as $kode => $findings) {
            $inspection = HseInspection::where('kode', $kode)->first();

            if (! $inspection) {
                $this->context->addError("Temuan: Kode HSE \"{$kode}\" tidak ditemukan (pastikan sheet HSE memuat kode tersebut).");

                continue;
            }

            $inspection->findings = HseInspection::normalizeFindings($findings);
            $inspection->save();
        }
    }
}
