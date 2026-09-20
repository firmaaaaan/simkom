<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DeviceCheck extends Model
{
    use HasUuids;
    protected $fillable = [
        'laboratory_id',
        'academic_year_id',
        'check_date',
        'officer_name',
        'notes',
    ];

    protected $casts = [
        // Format disimpan eksplisit agar kolom date tidak ikut menyimpan jam.
        'check_date' => 'date:Y-m-d',
    ];

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function items()
    {
        return $this->hasMany(DeviceCheckItem::class);
    }

    /**
     * Kolom item perangkat yang diperiksa — urutannya sama dengan urutan
     * kolom pada matriks. Tambah/kurangi entri di sini bila item berubah.
     */
    public static function itemColumns(): array
    {
        return [
            ['key' => 'mouse', 'label' => 'Mouse'],
            ['key' => 'keyboard', 'label' => 'Keyboard'],
            ['key' => 'hdmi', 'label' => 'HDMI'],
            ['key' => 'power_pc', 'label' => 'PC', 'group' => 'Kabel Power'],
            ['key' => 'power_cpu', 'label' => 'CPU', 'group' => 'Kabel Power'],
            ['key' => 'power_ups', 'label' => 'UPS', 'group' => 'Kabel Power'],
            ['key' => 'ups', 'label' => 'UPS'],
        ];
    }

    /**
     * Daftar key item saja, dipakai untuk validasi & penyimpanan.
     */
    public static function itemKeys(): array
    {
        return array_column(static::itemColumns(), 'key');
    }

    /**
     * Struktur header matriks: kolom tunggal (rowspan 2) dan kolom bergrup
     * (satu judul dengan beberapa sub-kolom, mis. "Kabel Power" → PC/CPU/UPS).
     */
    public static function headerGroups(): array
    {
        $header = [];

        foreach (static::itemColumns() as $column) {
            $group = $column['group'] ?? null;

            if ($group === null) {
                $header[] = ['type' => 'single', 'label' => $column['label']];

                continue;
            }

            $last = array_key_last($header);

            if ($last !== null && $header[$last]['type'] === 'group' && $header[$last]['label'] === $group) {
                $header[$last]['columns'][] = $column;

                continue;
            }

            $header[] = ['type' => 'group', 'label' => $group, 'columns' => [$column]];
        }

        return $header;
    }

    /**
     * Jumlah kolom item (bergrup dihitung per sub-kolom) — untuk colspan.
     */
    public static function itemColumnCount(): int
    {
        return count(static::itemColumns());
    }
}
