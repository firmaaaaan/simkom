<?php

namespace App\Imports;

use App\Imports\Concerns\InteractsWithK3Rows;
use App\Models\DamageReport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Sheet "Peralatan": daftar peralatan terdampak dikelompokkan berdasarkan
 * Kode Laporan, lalu mengganti daftar peralatan laporan terkait.
 */
class DamageEquipmentSheetImport implements ToCollection, WithHeadingRow
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
                'nama' => $this->text($rowArray['nama_peralatan'] ?? null),
                'merk' => $this->text($rowArray['merk'] ?? null),
                'no_inventaris' => $this->text($rowArray['no_inventaris'] ?? null),
                'jumlah' => $this->text($rowArray['jumlah'] ?? null),
                'keterangan' => $this->text($rowArray['keterangan'] ?? null),
            ];
        }

        foreach ($grouped as $kode => $peralatan) {
            $report = DamageReport::where('kode', $kode)->first();

            if (! $report) {
                $this->context->addError("Peralatan: Kode Laporan \"{$kode}\" tidak ditemukan (pastikan sheet Laporan memuat kode tersebut).");

                continue;
            }

            $report->peralatan = DamageReport::normalizePeralatan($peralatan);
            $report->save();
        }
    }
}
