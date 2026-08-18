<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Komputer extends Model
{
    protected $table = 'komputer';

    protected $fillable = [
        'kode_komputer',
        'nama_komputer',
        'laboratorium_id',
        'foto',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'foto' => 'string',
        ];
    }

    public function laboratorium()
    {
        return $this->belongsTo(Laboratorium::class, 'laboratorium_id');
    }

    public function hardware()
    {
        return $this->belongsToMany(Hardware::class, 'komputer_hardware', 'komputer_id', 'hardware_id')
            ->withPivot('jumlah')
            ->withTimestamps();
    }

    public function software()
    {
        return $this->belongsToMany(Software::class, 'komputer_software', 'komputer_id', 'software_id')
            ->withTimestamps();
    }

    /**
     * Spesifikasi dibuat otomatis dari hardware dan software yang dipilih.
     */
    public function getSpesifikasiAttribute(): string
    {
        $parts = [];

        if ($this->relationLoaded('hardware') && $this->hardware->isNotEmpty()) {
            $names = $this->hardware->pluck('nama_hardware')->filter()->all();
            $parts[] = 'Hardware: ' . implode(', ', $names);
        }

        if ($this->relationLoaded('software') && $this->software->isNotEmpty()) {
            $names = $this->software->pluck('nama_software')->filter()->all();
            $parts[] = 'Software: ' . implode(', ', $names);
        }

        if (!empty($parts)) {
            return implode(' | ', $parts);
        }

        return $this->attributes['spesifikasi'] ?? '';
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) {
            return null;
        }

        if (Storage::disk('public')->exists($this->foto)) {
            return asset('storage/' . $this->foto);
        }

        return $this->foto;
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'aktif' => 'bg-label-success',
            'tidak_aktif' => 'bg-label-secondary',
            'perbaikan' => 'bg-label-warning',
            'rusak' => 'bg-label-danger',
            default => 'bg-label-secondary',
        };
    }

    public function kartuKendali()
    {
        return $this->morphMany(KartuKendali::class, 'inspectable');
    }

    public function pemeliharaan()
    {
        return $this->hasMany(PemeliharaanKomputer::class);
    }

    public function peminjaman()
    {
        return $this->hasMany(PeminjamanKomputer::class);
    }
}
