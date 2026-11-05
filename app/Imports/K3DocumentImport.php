<?php

namespace App\Imports;

use App\Imports\Concerns\InteractsWithK3Rows;
use App\Models\K3Document;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import metadata dokumen K3 (tanpa berkas). Berkas dapat diunggah
 * menyusul melalui halaman edit.
 */
class K3DocumentImport implements ToModel, WithHeadingRow
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
            $required = [
                'judul' => $this->required($row['judul'] ?? null, 'Judul', $rowNumber),
                'kategori' => null,
            ];
            if ($required['judul'] === null) {
                return null;
            }

            $kategori = $this->resolveKategori($row['kategori'] ?? null);
            if (! $kategori) {
                $this->errors[] = 'Baris '.$rowNumber.': Kategori harus salah satu dari '.implode(', ', array_keys(K3Document::KATEGORI)).'.';

                return null;
            }
            $required['kategori'] = $kategori;

            $attributes = [
                'judul' => $required['judul'],
                'kategori' => $kategori,
                'nomor_dokumen' => $this->text($row['nomor_dokumen'] ?? null) ?: null,
                'tanggal_berlaku' => $this->parseDate($row['tanggal_berlaku'] ?? null),
                'tanggal_revisi' => $this->parseDate($row['tanggal_revisi'] ?? null),
                'keterangan' => $this->text($row['keterangan'] ?? null) ?: null,
                // Metadata saja; berkas diunggah manual melalui halaman edit.
                'file_name' => null,
                'file_path' => null,
                'file_extension' => null,
                'mime_type' => null,
                'file_size' => null,
            ];

            $kode = $this->text($row['kode'] ?? null);

            if ($kode !== '') {
                $document = K3Document::firstOrNew(['kode' => $kode]);
            } else {
                $document = new K3Document;
            }

            $isNew = ! $document->exists;
            $document->fill($attributes);

            if ($isNew) {
                $document->uploaded_by = Auth::id();
            }

            $document->save();
            $this->importedCount++;

            return $document;
        } catch (\Throwable $e) {
            $this->errors[] = "Baris {$rowNumber}: ".$e->getMessage();

            return null;
        }
    }

    private function resolveKategori(mixed $value): ?string
    {
        $text = $this->text($value);
        if ($text === '') {
            return null;
        }

        foreach (K3Document::KATEGORI as $key => $label) {
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
