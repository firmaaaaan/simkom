<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AparInspection extends Model
{
    use HasUuids;

    /**
     * Item pemeriksaan sesuai Form 01 - Lembar Periksa APAR.
     * Label Inggris dipertahankan seperti pada form resmi, disertai padanan Indonesia.
     */
    public const ITEMS = [
        'restraining_wire' => ['label' => 'Restraining Wire', 'label_id' => 'Kawat Segel'],
        'pin' => ['label' => 'Pin', 'label_id' => 'Pin'],
        'pressure_gauge' => ['label' => 'Pressure Gauge', 'label_id' => 'Penunjuk Tekanan'],
        'cartridge_condition' => ['label' => 'Cartridge Condition', 'label_id' => 'Kondisi Catridge'],
        'chemical_powder' => ['label' => 'Cemichal Powder', 'label_id' => 'Tepung Kimia'],
        'hoses_condition' => ['label' => 'Hoses Condition', 'label_id' => 'Kondisi Selang'],
        'bottle_condition' => ['label' => 'Bottle Condition', 'label_id' => 'Kondisi Botol'],
    ];

    public const STATUSES = [
        'baik' => 'Baik',
        'tidak' => 'Tidak',
    ];

    /** Identitas dokumen Form 01 (tetap, mengikuti form resmi K3 Lab). */
    public const DOC_NUMBER = '001/K3.Lab/UNISA/XI/2024';

    public const DOC_DATE = '15 November 2024';

    public const KOORDINATOR_NAME = 'Asih Puji Utami, S.KM., M.Kes.';

    public const KOORDINATOR_ROLE = 'Koordinator K3 Laboratorium';

    protected $fillable = [
        'kode',
        'nomor_registrasi',
        'tanggal_inspeksi',
        'lokasi',
        'items',
        'diperiksa_oleh',
        'created_by',
    ];

    protected $casts = [
        'tanggal_inspeksi' => 'date:Y-m-d',
        'items' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (AparInspection $inspection) {
            if (empty($inspection->kode)) {
                $inspection->kode = self::generateKode();
            }
        });
    }

    public static function generateKode(): string
    {
        do {
            $code = 'APAR-'.strtoupper(substr(uniqid(), -8));
        } while (static::where('kode', $code)->exists());

        return $code;
    }

    /**
     * Susun hasil pemeriksaan dari input form. Hanya key item yang dikenal
     * yang disimpan, sehingga jumlah dan urutan item selalu konsisten.
     */
    public static function normalizeItems(array $input): array
    {
        $items = [];

        foreach (array_keys(self::ITEMS) as $key) {
            $items[$key] = [
                'status' => ($input[$key]['status'] ?? 'baik') === 'tidak' ? 'tidak' : 'baik',
                'keterangan' => trim((string) ($input[$key]['keterangan'] ?? '')),
            ];
        }

        return $items;
    }

    /** Baris item lengkap dengan label, status, dan keterangan untuk tampilan. */
    public function getItemRowsAttribute(): array
    {
        $stored = $this->items ?? [];

        return collect(self::ITEMS)
            ->map(fn (array $item, string $key) => [
                'key' => $key,
                'label' => $item['label'],
                'label_id' => $item['label_id'],
                'status' => ($stored[$key]['status'] ?? 'baik') === 'tidak' ? 'tidak' : 'baik',
                'keterangan' => (string) ($stored[$key]['keterangan'] ?? ''),
            ])
            ->values()
            ->all();
    }

    public function getJumlahTidakBaikAttribute(): int
    {
        return collect($this->item_rows)->where('status', 'tidak')->count();
    }

    /** Kondisi keseluruhan unit APAR pada pemeriksaan ini. */
    public function getKondisiAttribute(): string
    {
        return $this->jumlah_tidak_baik > 0 ? 'Ada Temuan' : 'Baik';
    }

    /** Nama hari dalam bahasa Indonesia, mis. "Senin". */
    public function getHariAttribute(): string
    {
        return $this->tanggal_inspeksi?->translatedFormat('l') ?? '-';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, fn (Builder $q) => $q
            ->where('kode', 'like', "%{$search}%")
            ->orWhere('nomor_registrasi', 'like', "%{$search}%")
            ->orWhere('lokasi', 'like', "%{$search}%")
            ->orWhere('diperiksa_oleh', 'like', "%{$search}%"));
    }

    public function toPrintArray(): array
    {
        return [
            'kode' => $this->kode,
            'nomor_registrasi' => $this->nomor_registrasi,
            'tanggal_inspeksi' => $this->tanggal_inspeksi?->toDateString(),
            'lokasi' => $this->lokasi,
            'diperiksa_oleh' => $this->diperiksa_oleh,
            'item_rows' => $this->item_rows,
        ];
    }
}
