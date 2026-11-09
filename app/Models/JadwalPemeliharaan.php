<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JadwalPemeliharaan extends Model
{
    use HasUuids;

    public const DOC_NUMBER = 'FM-UNISA-LAB-PBM-03-02';

    public const JENIS_PEMELIHARAAN = [
        'Pembersihan',
        'Pengecekan fungsi',
        'Pelumasan',
        'Penggantian komponen',
        'Sterilisasi',
        'Pengondisian/penyimpanan',
        'Lainnya',
    ];

    public const FREKUENSI = [
        'Harian',
        'Mingguan',
        'Bulanan',
        '6 Bulanan',
        'Tahunan',
        'Sesuai kebutuhan',
    ];

    protected $fillable = [
        'kode_dokumen',
        'laboratory_id',
        'laboratorium_name',
        'academic_year_id',
        'periode',
        'pic',
        'tanggal_penyusunan',
        'disusun_oleh',
        'disusun_tanggal',
        'diperiksa_oleh',
        'diperiksa_tanggal',
        'mengetahui_oleh',
        'mengetahui_tanggal',
        'created_by',
    ];

    protected $casts = [
        'tanggal_penyusunan' => 'date:Y-m-d',
        'disusun_tanggal' => 'date:Y-m-d',
        'diperiksa_tanggal' => 'date:Y-m-d',
        'mengetahui_tanggal' => 'date:Y-m-d',
    ];

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(JadwalPemeliharaanItem::class)->orderBy('sort_order')->orderBy('created_at');
    }

    public function getNamaLabAttribute(): string
    {
        return $this->laboratorium_name ?: ($this->laboratory?->name ?? '-');
    }

    public function getPeriodeLabelAttribute(): string
    {
        return $this->academicYear?->name ?? ($this->periode ?: '-');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $q) use ($search) {
            $q->where('periode', 'like', "%{$search}%")
                ->orWhere('pic', 'like', "%{$search}%")
                ->orWhere('laboratorium_name', 'like', "%{$search}%")
                ->orWhereHas('items', fn (Builder $i) => $i
                    ->where('nama_alat_bahan', 'like', "%{$search}%")
                    ->orWhere('kode_inventaris', 'like', "%{$search}%"));
        });
    }

    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        return $query->when($status, fn (Builder $q) => $q->whereHas('items', fn (Builder $i) => $i->where('status', $status)));
    }
}
