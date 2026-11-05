<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class K3Document extends Model
{
    use HasUuids;

    public const KATEGORI = [
        'form' => 'Formulir',
        'sop' => 'SOP',
        'peraturan' => 'Peraturan',
        'pelatihan' => 'Pelatihan',
        'laporan' => 'Laporan',
        'lain-lain' => 'Lain-lain',
    ];

    protected $fillable = [
        'kode',
        'judul',
        'kategori',
        'nomor_dokumen',
        'tanggal_berlaku',
        'tanggal_revisi',
        'keterangan',
        'file_name',
        'file_path',
        'file_extension',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    protected $casts = [
        'tanggal_berlaku' => 'date:Y-m-d',
        'tanggal_revisi' => 'date:Y-m-d',
    ];

    protected static function booted(): void
    {
        static::creating(function (K3Document $document) {
            if (empty($document->kode)) {
                $document->kode = self::generateKode();
            }
        });
    }

    public static function generateKode(): string
    {
        do {
            $code = 'DOK-K3-'.strtoupper(substr(uniqid(), -8));
        } while (static::where('kode', $code)->exists());

        return $code;
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getKategoriLabelAttribute(): string
    {
        return self::KATEGORI[$this->kategori] ?? ucfirst($this->kategori);
    }

    public function getFileSizeHumanAttribute(): string
    {
        if (! $this->file_size) {
            return '-';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, fn (Builder $q) => $q
            ->where('kode', 'like', "%{$search}%")
            ->orWhere('judul', 'like', "%{$search}%")
            ->orWhere('nomor_dokumen', 'like', "%{$search}%"));
    }

    public function scopeKategori(Builder $query, ?string $kategori): Builder
    {
        return $query->when($kategori && $kategori !== 'semua', fn (Builder $q) => $q->where('kategori', $kategori));
    }
}
