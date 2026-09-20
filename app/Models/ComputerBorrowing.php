<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ComputerBorrowing extends Model
{
    use HasUuids;
    protected $fillable = [
        'tracking_code',
        'computer_id',
        'laboratory_id',
        'borrower_name',
        'borrower_nim',
        'borrower_prodi',
        'purpose',
        'borrow_date',
        'borrow_time_start',
        'borrow_time_end',
        'status',
        'admin_notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (ComputerBorrowing $borrowing) {
            if (empty($borrowing->tracking_code)) {
                $borrowing->tracking_code = self::generateTrackingCode();
            }
        });

        // Notifikasi realtime untuk pengajuan peminjaman baru dari halaman publik
        static::created(function (ComputerBorrowing $borrowing) {
            Notification::create([
                'title' => 'Pengajuan Peminjaman Baru',
                'message' => "Peminjaman {$borrowing->tracking_code} - {$borrowing->borrower_name}",
                'type' => 'borrowing',
                'url' => route('borrowings.index'),
            ]);
        });
    }

    public static function generateTrackingCode(): string
    {
        do {
            $code = 'BMJ-' . strtoupper(substr(uniqid(), -8));
        } while (static::where('tracking_code', $code)->exists());

        return $code;
    }

    public function computer()
    {
        return $this->belongsTo(Computer::class);
    }

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class);
    }
}
