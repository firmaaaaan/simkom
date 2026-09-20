<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Ticket extends Model
{
    use HasUuids;
    protected $fillable = [
        'tracking_code',
        'laboratory_id',
        'computer_id',
        'academic_year_id',
        'reported_by',
        'assigned_to',
        'reporter_name',
        'reporter_nim',
        'reporter_prodi',
        'category',
        'title',
        'description',
        'images',
        'priority',
        'status',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            if (empty($ticket->tracking_code)) {
                $ticket->tracking_code = self::generateTrackingCode();
            }
        });

        // Notifikasi realtime untuk tiket baru yang dilaporkan publik/user
        static::created(function (Ticket $ticket) {
            Notification::create([
                'title' => 'Laporan Kendala Baru',
                'message' => "Tiket {$ticket->tracking_code} - {$ticket->title}",
                'type' => 'ticket',
                'url' => route('tickets.show', $ticket),
            ]);
        });
    }

    public static function generateTrackingCode(): string
    {
        do {
            $code = 'TKT-' . strtoupper(substr(uniqid(), -8));
        } while (static::where('tracking_code', $code)->exists());

        return $code;
    }

    /**
     * Saring tiket berdasarkan bulan dan/atau tahun. Tanpa keduanya = tanpa filter.
     */
    public function scopeForPeriod(Builder $query, ?int $month, ?int $year): Builder
    {
        return $query
            ->when($month >= 1 && $month <= 12, fn (Builder $q) => $q->whereMonth('created_at', $month))
            ->when($year >= 1900 && $year <= 2999, fn (Builder $q) => $q->whereYear('created_at', $year));
    }

    /**
     * Tahun yang bisa dipilih pada filter/grafik (terbaru di depan), minimal tahun berjalan.
     */
    public static function availableYears(): array
    {
        $range = static::query()
            ->selectRaw('MIN(created_at) as min_created, MAX(created_at) as max_created')
            ->first();

        $current = (int) now()->year;

        if (! $range?->min_created) {
            return [$current];
        }

        $oldest = Carbon::parse($range->min_created)->year;
        $newest = max(Carbon::parse($range->max_created)->year, $current);

        return range($newest, $oldest);
    }

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function computer()
    {
        return $this->belongsTo(Computer::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class)->with('user')->oldest();
    }
}
