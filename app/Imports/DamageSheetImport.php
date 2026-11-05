<?php

namespace App\Imports;

use App\Imports\Concerns\InteractsWithK3Rows;
use App\Models\DamageReport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/** Sheet "Laporan": data utama laporan kerusakan/kehilangan/pencurian (Form 007). */
class DamageSheetImport implements ToModel, WithHeadingRow
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
            $tanggalInsiden = $this->parseDate($row['tanggal_insiden'] ?? null);
            if (! $tanggalInsiden) {
                $this->context->addError("Baris {$rowNumber}: Tanggal Insiden wajib diisi (format tanggal valid).");

                return null;
            }

            $required = [
                'pelapor_nama' => $this->required($row['nama_pelapor'] ?? null, 'Nama Pelapor', $rowNumber),
                'pelapor_ruangan' => $this->required($row['ruangan'] ?? null, 'Ruangan', $rowNumber),
                'jenis_kejadian' => null,
                'deskripsi_kejadian' => $this->required($row['deskripsi_kejadian'] ?? null, 'Deskripsi Kejadian', $rowNumber),
            ];

            $jenis = $this->resolveJenis($row['jenis_kejadian'] ?? null);
            if (! $jenis) {
                $this->context->addError("Baris {$rowNumber}: Jenis Kejadian harus Kerusakan, Kehilangan, atau Pencurian.");

                return null;
            }
            $required['jenis_kejadian'] = $jenis;

            if (in_array(null, $required, true)) {
                return null;
            }

            $attributes = [
                'pelapor_nama' => $required['pelapor_nama'],
                'pelapor_ruangan' => $required['pelapor_ruangan'],
                'pelapor_telepon' => $this->text($row['telepon'] ?? null) ?: null,
                'tanggal_insiden' => $tanggalInsiden,
                'waktu_kejadiaan' => $this->parseTime($row['waktu_kejadian'] ?? null),
                'tanggal_dilaporkan' => $this->parseDate($row['tanggal_dilaporkan'] ?? null),
                'waktu_dilaporkan' => $this->parseTime($row['waktu_dilaporkan'] ?? null),
                'lokasi_spesifik' => $this->text($row['lokasi_spesifik'] ?? null) ?: null,
                'jenis_kejadian' => $jenis,
                'deskripsi_kejadian' => $required['deskripsi_kejadian'],
                'perkiraan_biaya' => $this->text($row['perkiraan_biaya'] ?? null) ?: null,
                'penanggung_jawab' => $this->text($row['penanggung_jawab'] ?? null) ?: null,
                'dilaporkan_ke_polisi' => $this->parseYesNo($row['dilaporkan_ke_polisi'] ?? $row['dilaporkan_ke_polisi_ya_tidak'] ?? $row['dilaporkan_ke_polisi_yatidak'] ?? null),
                'berkas_polisi' => $this->text($row['berkas_polisi'] ?? null) ?: null,
                'petugas_polisi' => $this->text($row['petugas_polisi'] ?? null) ?: null,
                'unit_kepolisian' => $this->text($row['unit_kepolisian'] ?? null) ?: null,
                'telepon_polisi' => $this->text($row['telepon_polisi'] ?? null) ?: null,
            ];

            $kode = $this->text($row['kode'] ?? null);

            if ($kode !== '') {
                $report = DamageReport::firstOrNew(['kode' => $kode]);
            } else {
                $report = new DamageReport;
            }

            $isNew = ! $report->exists;
            $report->fill($attributes);

            if ($isNew) {
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

    private function resolveJenis(mixed $value): ?string
    {
        $text = $this->text($value);
        if ($text === '') {
            return null;
        }

        foreach (DamageReport::JENIS as $key => $label) {
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
            $this->context->addError("Baris {$rowNumber}: {$field} wajib diisi.");

            return null;
        }

        return $text;
    }
}
