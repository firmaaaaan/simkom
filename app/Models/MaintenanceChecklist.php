<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceChecklist extends Model
{
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
