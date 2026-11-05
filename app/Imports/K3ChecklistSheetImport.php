<?php

namespace App\Imports;

use App\Imports\Concerns\InteractsWithK3Rows;
use App\Models\K3InspectionReport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Sheet "Checklist": hasil periksa item 1-33 dikelompokkan berdasarkan
 * Kode Laporan, lalu mengganti checklist laporan terkait.
 */
class K3ChecklistSheetImport implements ToCollection, WithHeadingRow
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

            $no = (int) ($rowArray['no_item'] ?? 0);
            if ($no < 1 || $no > count(K3InspectionReport::CHECKLIST_ITEMS)) {
                $this->context->addError('Checklist baris '.($index + 2).': No Item harus angka 1-'.count(K3InspectionReport::CHECKLIST_ITEMS).'.');

                continue;
            }

            $hasil = strtolower($this->text($rowArray['hasil'] ?? $rowArray['hasil_ya_tidak_na'] ?? $rowArray['hasil_yatidakna'] ?? null));
            if (! in_array($hasil, ['ya', 'tidak', 'na'], true)) {
                $this->context->addError('Checklist baris '.($index + 2).": Hasil harus Ya, Tidak, atau NA (ditemukan: \"{$hasil}\").");

                continue;
            }

            $grouped[$kode][$no - 1] = [
                'ya' => $hasil === 'ya',
                'tidak' => $hasil === 'tidak',
                'na' => $hasil === 'na',
                'catatan' => $this->text($rowArray['catatan'] ?? null),
            ];
        }

        foreach ($grouped as $kode => $items) {
            $report = K3InspectionReport::where('kode', $kode)->first();

            if (! $report) {
                $this->context->addError("Checklist: Kode Laporan \"{$kode}\" tidak ditemukan (pastikan sheet Laporan memuat kode tersebut).");

                continue;
            }

            $report->checklist_items = K3InspectionReport::normalizeChecklist($items);
            $report->save();
        }
    }
}
