<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Signature extends Model
{
    protected $fillable = [
        'name',
        'role',
        'image',
    ];

    /**
     * Laporan cetak yang bisa menerima TTD (key => label).
     * Key dipakai di tabel report_signature dan pada @include partial.
     */
    public const REPORTS = [
        'inventory-print' => 'Laporan Inventaris Laboratorium',
        'laporan' => 'Laporan Umum (PDF/ZIP)',
        'card-control-print' => 'Laporan Kartu Kendali',
        'card-print' => 'Kartu Kendali per Komputer',
        'maintenance-print' => 'Laporan Maintenance',
        'device-check-report-print' => 'Laporan Pengecekan Alat',
        'device-check-print' => 'Cetak Pengecekan per Alat',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(ReportSignature::class);
    }

    /**
     * TTD yang ditugaskan ke satu laporan. Hasil di-cache per-request
     * agar satu halaman cetak dengan beberapa blok TTD tidak query berulang.
     */
    /** Cache per-process: satu halaman cetak dengan beberapa blok TTD tidak query berulang. */
    private static array $reportCache = [];

    public static function forReport(string $reportKey): ?self
    {
        if (! array_key_exists($reportKey, self::$reportCache)) {
            self::$reportCache[$reportKey] = static::query()
                ->whereHas('assignments', fn ($query) => $query->where('report_key', $reportKey))
                ->first();
        }

        return self::$reportCache[$reportKey];
    }

    /** Reset cache penugasan — dipakai test yang mengubah data pada proses yang sama. */
    public static function flushReportCache(): void
    {
        self::$reportCache = [];
    }

    /**
     * Data URI (base64) gambar TTD — aman dipakai di browser maupun DomPDF
     * yang tidak bisa memuat http://asset() saat render PDF.
     */
    public function imageSrc(): ?string
    {
        if (! $this->image) {
            return null;
        }

        $path = Storage::disk('public')->path($this->image);

        if (! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }

    /** Label laporan yang sedang ditugaskan ke TTD ini (untuk badge). */
    public function reportLabels(): array
    {
        return $this->assignments
            ->map(fn (ReportSignature $assignment) => self::REPORTS[$assignment->report_key] ?? $assignment->report_key)
            ->values()
            ->all();
    }
}
