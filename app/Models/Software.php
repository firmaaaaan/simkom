<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Software extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'code',
        'version',
        'license_type',
        'provider',
        'download_link',
        'category',
        'status',
        'description',
        'pic_name',
        'pic_email',
    ];

    public static function generateCode(): string
    {
        $last = static::where('code', 'like', 'SW-%')
            ->orderByRaw('CAST(SUBSTR(code, 4) AS UNSIGNED) DESC')
            ->value('code');

        $next = 1;
        if ($last && preg_match('/^SW-(\d+)$/', $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return 'SW-'.str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function computers(): BelongsToMany
    {
        return $this->belongsToMany(Computer::class, 'computer_software')->withTimestamps();
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(SoftwareLicense::class);
    }

    public function getAssignedLicensesCountAttribute(): int
    {
        return $this->licenses->sum(fn (SoftwareLicense $license) => $license->usedSeats());
    }

    public function getTotalSeatsAttribute(): int
    {
        return $this->licenses->sum(fn (SoftwareLicense $license) => $license->max_devices);
    }

    /**
     * @return list<string>
     */
    public function licensedComputerIds(): array
    {
        return DB::table('software_license_computer')
            ->join('software_licenses', 'software_licenses.id', '=', 'software_license_computer.software_license_id')
            ->where('software_licenses.software_id', $this->id)
            ->distinct()
            ->pluck('computer_id')
            ->all();
    }
}
