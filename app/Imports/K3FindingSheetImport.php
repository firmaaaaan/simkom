<?php

namespace App\Imports;

use App\Imports\Concerns\InteractsWithK3Rows;
use App\Models\K3InspectionReport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Sheet "Temuan": baris temuan inspeksi dikelompokkan berdasarkan
 * Kode Laporan, lalu mengganti temuan laporan terkait.
 */
class K3FindingSheetImport implements ToCollection, WithHeadingRow
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

            $kode = $this->text($rowArray['kode_laporan'] ?? null);
            if ($kode === '' || $this->isContohRow($kode)) {
                continue;
            }

            $grouped[$kode][] = [
                'temuan' => $this->text($rowArray['temuan'] ?? null),
                'dokumentasi' => $this->text($rowArray['dokumentasi'] ?? null),
                'saran_perbaikan' => $this->text($rowArray['saran_perbaikan'] ?? null),
                'pelaksana' => $this->text($rowArray['pelaksana'] ?? null),
                'target' => $this->text($rowArray['target'] ?? null),
                'catatan' => $this->text($rowArray['catatan'] ?? null),
            ];
        }

        foreach ($grouped as $kode => $findings) {
            $report = K3InspectionReport::where('kode', $kode)->first();

            if (! $report) {
                $this->context->addError("Temuan: Kode Laporan \"{$kode}\" tidak ditemukan (pastikan sheet Laporan memuat kode tersebut).");

                continue;
            }

            $report->findings = K3InspectionReport::normalizeFindings($findings);
            $report->save();
        }
    }
}
