<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MaintenanceChecklist extends Model
{
    use HasUuids;

    protected $fillable = [
        'laboratory_id',
        'academic_year_id',
        'maintenance_date',
        'inspector_name',
        'notes_computer',
        'notes_mouse_keyboard',
        'notes_ups',
        'notes_monitor',
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
        return $this->hasMany(MaintenanceChecklistItem::class);
    }

    /**
     * Riwayat pemeliharaan yang mencakup satu komputer (baris item milik
     * komputer ikut dimuat, hanya untuk komputer itu) — terbaru lebih dulu.
     */
    public function scopeForComputer($query, string $computerId)
    {
        return $query
            ->whereHas('items', fn ($q) => $q->where('computer_id', $computerId))
            ->with([
                'laboratory',
                'academicYear',
                'items' => fn ($q) => $q->where('computer_id', $computerId),
            ])
            ->orderByDesc('maintenance_date')
            ->orderByDesc('id')
            ->limit(20);
    }

    public static function getChecklistItems(): array
    {
        return [
            'A' => [
                'name' => 'Pemeriksaan Komputer',
                'items' => [
                    'Apakah kondisi komputer normal sebelum dilakukan pemeliharaan?',
                    'Apakah bagian dalam casing komputer telah dibersihkan dari debu?',
                    'Apakah komputer telah dilakukan pemeliharaan?',
                    'Apakah Harddisk telah diperiksa?',
                    'Apakah RAM telah diperiksa?',
                    'Apakah thermal paste pada CPU telah diganti?',
                    'Apakah kondisi komputer normal setelah dilakukan pemeliharaan?',
                ],
            ],
            'B' => [
                'name' => 'Pemeriksaan Mouse dan Keyboard',
                'items' => [
                    'Apakah mouse dan keyboard dalam keadaan lengkap?',
                    'Apakah mouse dan keyboard dapat digunakan dengan baik?',
                ],
            ],
            'C' => [
                'name' => 'Pemeriksaan UPS',
                'items' => [
                    'Apakah baterai UPS telah diperiksa dalam kondisi baik?',
                ],
            ],
            'D' => [
                'name' => 'Pemeriksaan Monitor',
                'items' => [
                    'Apakah Monitor dalam keadaan baik?',
                ],
            ],
        ];
    }
}
