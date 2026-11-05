<?php

namespace App\Imports\Concerns;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Helper umum untuk import Excel modul K3: parsing tanggal/waktu Excel,
 * konversi Ya/Tidak, dan deteksi baris contoh pada template.
 */
trait InteractsWithK3Rows
{
    protected function text(mixed $value): string
    {
        if (is_array($value)) {
            return '';
        }

        return trim((string) ($value ?? ''));
    }

    /** Baris contoh pada template (kode "Contoh") dilewati saat import. */
    protected function isContohRow(mixed $kode): bool
    {
        return strcasecmp($this->text($kode), 'Contoh') === 0;
    }

    /** Konversi baris dari sheet (array atau Collection) menjadi array asosiatif. */
    protected function toRowArray(mixed $row): array
    {
        if (is_array($row)) {
            return $row;
        }

        if (is_object($row) && method_exists($row, 'toArray')) {
            return $row->toArray();
        }

        return (array) $row;
    }

    /** Baris benar-benar kosong dilewati tanpa mencatat error. */
    protected function isBlankRow(array $row): bool
    {
        foreach ($row as $value) {
            if (is_array($value)) {
                continue;
            }

            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Parse nilai tanggal dari Excel: serial number Excel, DateTime,
     * atau string bebas (Y-m-d, d/m/Y, dll). Return 'Y-m-d' atau null.
     */
    protected function parseDate(mixed $value): ?string
    {
        if (! is_scalar($value) && ! $value instanceof \DateTimeInterface) {
            return null;
        }

        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            if ($value instanceof \DateTimeInterface) {
                return Carbon::instance($value)->format('Y-m-d');
            }

            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
            }

            return Carbon::parse(trim((string) $value))->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /** Parse nilai jam dari Excel: fraksi hari serial Excel atau "HH:MM". */
    protected function parseTime(mixed $value): ?string
    {
        if (! is_scalar($value) && ! $value instanceof \DateTimeInterface) {
            return null;
        }

        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            if ($value instanceof \DateTimeInterface) {
                return $value->format('H:i');
            }

            if (is_numeric($value) && $value < 1) {
                $seconds = (int) round(((float) $value) * 86400);

                return gmdate('H:i', $seconds);
            }

            $time = Carbon::parse(trim((string) $value));

            return $time->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    /** Konversi sel "Ya"/"Tidak" (dan variasinya) menjadi boolean. */
    protected function parseYesNo(mixed $value, bool $default = false): bool
    {
        $text = strtolower($this->text($value));

        if ($text === '') {
            return $default;
        }

        return in_array($text, ['ya', 'y', '1', 'true', 'iya', 'yes'], true);
    }
}
