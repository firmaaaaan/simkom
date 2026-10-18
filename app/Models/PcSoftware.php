<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PcSoftware extends Model
{
    use HasUuids;

    /** "software" dianggap uncountable oleh inflector — set explicit. */
    protected $table = 'pc_softwares';

    protected $fillable = [
        'pc_monitor_id',
        'name',
        'version',
        'publisher',
        'install_date',
        'size_kb',
    ];

    public function pcMonitor(): BelongsTo
    {
        return $this->belongsTo(PcMonitor::class);
    }
}
