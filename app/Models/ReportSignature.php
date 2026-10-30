<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penugasan TTD ke satu laporan. report_key adalah primary key karena
 * satu laporan hanya boleh ditugasi satu TTD.
 */
class ReportSignature extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'report_key';

    protected $keyType = 'string';

    protected $fillable = [
        'report_key',
        'signature_id',
    ];

    public function signature(): BelongsTo
    {
        return $this->belongsTo(Signature::class);
    }
}
