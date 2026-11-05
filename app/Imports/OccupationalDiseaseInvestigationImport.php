<?php

namespace App\Imports;

use App\Imports\Concerns\InteractsWithK3Rows;
use App\Models\OccupationalDiseaseInvestigation;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OccupationalDiseaseInvestigationImport implements ToModel, WithHeadingRow
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
            $nama = $this->text($row['nama'] ?? null);
            if ($nama === '') {
                $this->errors[] = "Baris {$rowNumber}: Nama wajib diisi.";

                return null;
            }

            $jenisKelamin = $this->text($row['jenis_kelamin'] ?? null);
            if ($jenisKelamin !== '' && ! in_array($jenisKelamin, ['Laki-laki', 'Perempuan'], true)) {
                $this->errors[] = "Baris {$rowNumber}: Jenis Kelamin harus Laki-laki atau Perempuan.";

                return null;
            }

            $attributes = [
                'nama' => $nama,
                'nip' => $this->text($row['nip'] ?? null) ?: null,
                'bagian' => $this->text($row['bagian'] ?? null) ?: null,
                'jabatan' => $this->text($row['jabatan'] ?? null) ?: null,
                'jenis_kelamin' => $jenisKelamin !== '' ? $jenisKelamin : null,
                'lama_bekerja' => $this->text($row['lama_bekerja'] ?? null) ?: null,
                'keluhan' => $this->text($row['keluhan'] ?? null) ?: null,
                'riwayat_penyakit' => $this->text($row['riwayat_penyakit'] ?? null) ?: null,
                'riwayat_penyakit_keluarga' => $this->text($row['riwayat_penyakit_keluarga'] ?? null) ?: null,
                'riwayat_pekerjaan' => $this->text($row['riwayat_pekerjaan'] ?? null) ?: null,
                'pemeriksaan_fisik' => $this->text($row['pemeriksaan_fisik'] ?? null) ?: null,
                'pemeriksaan_radiologi' => $this->text($row['pemeriksaan_radiologi'] ?? null) ?: null,
                'pemeriksaan_laboratorium' => $this->text($row['pemeriksaan_laboratorium'] ?? null) ?: null,
                'pemeriksaan_penunjang' => $this->text($row['pemeriksaan_penunjang'] ?? null) ?: null,
                'pemeriksaan_patologi_anatomi' => $this->text($row['pemeriksaan_patologi_anatomi'] ?? null) ?: null,
                'perbandingan_pemeriksaan_awal' => $this->text($row['perbandingan_pemeriksaan_awal'] ?? null) ?: null,
                'perbandingan_pemeriksaan_berkala' => $this->text($row['perbandingan_pemeriksaan_berkala'] ?? null) ?: null,
                'tanggal_pemeriksaan' => $this->parseDate($row['tanggal_pemeriksaan'] ?? null),
                'diperiksa_oleh' => $this->text($row['diperiksa_oleh'] ?? null) ?: null,
            ];

            $kode = $this->text($row['kode'] ?? null);

            if ($kode !== '') {
                $investigation = OccupationalDiseaseInvestigation::firstOrNew(['kode' => $kode]);
            } else {
                $investigation = new OccupationalDiseaseInvestigation;
            }

            $isNew = ! $investigation->exists;
            $investigation->fill($attributes);

            if ($isNew) {
                $investigation->created_by = Auth::id();
            }

            $investigation->save();
            $this->importedCount++;

            return $investigation;
        } catch (\Throwable $e) {
            $this->errors[] = "Baris {$rowNumber}: ".$e->getMessage();

            return null;
        }
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
