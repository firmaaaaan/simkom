<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Computer extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'barcode',
        'laboratory_id',
        'status',
        'maintenance_reason',
        'description',
    ];

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function hardware(): BelongsToMany
    {
        return $this->belongsToMany(Hardware::class, 'computer_hardware')->withTimestamps();
    }

    /**
     * Hardware komputer dikelompokkan per kategori untuk tampilan detail.
     *
     * @return array<int, array{label: string, category: string, items: Collection}>
     */
    public function hardwareGroupedByCategory(): array
    {
        return Hardware::groupCategories($this->hardware);
    }

    public function software(): BelongsToMany
    {
        return $this->belongsToMany(Software::class, 'computer_software')->withTimestamps();
    }

    public function softwareLicenses(): HasMany
    {
        return $this->hasMany(SoftwareLicense::class, 'assigned_computer_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function hasOpenTickets(): bool
    {
        return $this->tickets()
            ->whereIn('status', ['Open', 'In Progress'])
            ->exists();
    }

    public function syncStatusFromTickets(): void
    {
        $hasOpen = $this->hasOpenTickets();

        if ($hasOpen && $this->status === 'Aktif') {
            $this->update(['status' => 'Maintenance', 'maintenance_reason' => 'ticket']);
        } elseif (! $hasOpen && $this->status === 'Maintenance' && $this->maintenance_reason === 'ticket') {
            $this->update(['status' => 'Aktif', 'maintenance_reason' => null]);
        }
    }
}
