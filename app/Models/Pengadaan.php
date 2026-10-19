<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Pengadaan extends Model
{
    use HasUuids;

    public const KATEGORIS = ['Software', 'Hardware', 'PC'];

    public const STATUSES = ['Proses', 'Acc', 'Ditolak'];

    protected $fillable = [
        'kode',
        'nama_barang',
        'kategori',
        'jumlah',
        'harga',
        'tanggal',
        'keterangan',
        'status',
        'academic_year_id',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'jumlah' => 'integer',
        'harga' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Pengadaan $pengadaan) {
            if (empty($pengadaan->kode)) {
                $pengadaan->kode = self::generateKode();
            }
        });
    }

    public static function generateKode(): string
    {
        do {
            $code = 'PGD-'.strtoupper(substr(uniqid(), -8));
        } while (static::where('kode', $code)->exists());

        return $code;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, fn (Builder $q) => $q
            ->where('nama_barang', 'like', "%{$search}%")
            ->orWhere('kode', 'like', "%{$search}%"));
    }

    public function scopeKategori(Builder $query, ?string $kategori): Builder
    {
        return $query->when($kategori, fn (Builder $q) => $q->where('kategori', $kategori));
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when($status, fn (Builder $q) => $q->where('status', $status));
    }

    public function getTotalAttribute(): int
    {
        return $this->jumlah * $this->harga;
    }
}
