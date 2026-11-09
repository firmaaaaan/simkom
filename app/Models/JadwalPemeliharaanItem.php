<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JadwalPemeliharaanItem extends Model
{
    use HasUuids;

    public const STATUSES = [
        'Terjadwal' => 'Terjadwal',
        'Selesai' => 'Selesai',
        'Tunda' => 'Tunda',
    ];

    protected $fillable = [
        'jadwal_pemeliharaan_id',
        'nama_alat_bahan',
        'kode_inventaris',
        'lokasi',
        'jenis_pemeliharaan',
        'frekuensi',
        'jadwal',
        'pic',
        'status',
        'catatan_hasil',
        'sort_order',
    ];

    protected $casts = [
        'jadwal' => 'date:Y-m-d',
    ];

    public function getKodeListAttribute(): array
    {
        $raw = (string) ($this->kode_inventaris ?? '');

        return array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $raw))));
    }

    public function getKodeLabelAttribute(): string
    {
        $list = $this->kode_list;

        return $list ? implode(', ', $list) : '-';
    }

    /**
     * Normalisasi input kode (string "A, B" / array dari multi-select) ke
     * string pisa koma yang disimpan di kolom text.
     */
    public static function normalizeKode($input): ?string
    {
        $parts = [];

        if (is_array($input)) {
            foreach ($input as $v) {
                foreach (preg_split('/[\r\n,]+/', (string) $v) as $p) {
                    $p = trim($p);
                    if ($p !== '') {
                        $parts[] = $p;
                    }
                }
            }
        } elseif ($input !== null && (string) $input !== '') {
            foreach (preg_split('/[\r\n,]+/', (string) $input) as $p) {
                $p = trim($p);
                if ($p !== '') {
                    $parts[] = $p;
                }
            }
        }

        $parts = array_values(array_unique($parts));

        return $parts ? implode(', ', $parts) : null;
    }

    public function jadwalPemeliharaan()
    {
        return $this->belongsTo(JadwalPemeliharaan::class);
    }

    public static function statusBadge(string $status): string
    {
        return match ($status) {
            'Selesai' => 'bg-green-100 text-green-700',
            'Terjadwal' => 'bg-blue-100 text-blue-700',
            default => 'bg-amber-100 text-amber-700',
        };
    }
}
