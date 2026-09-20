<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasUuids;
    protected $fillable = [
        'title',
        'message',
        'type',
        'url',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Notifikasi yang belum dibaca oleh user tertentu — membandingkan read_at global
     * dengan notifications_read_at milik user (pola read pointer, hemat kolom & query).
     */
    public function scopeUnreadFor(Builder $query, User $user): Builder
    {
        return $query->whereNull('read_at')->orWhere(function (Builder $q) use ($user) {
            $q->whereNotNull('read_at')->where('read_at', '>', $user->notifications_read_at ?? now(0));
        });
    }

    /**
     * Tandai semua notifikasi yang ada saat ini sudah dibaca oleh user:
     * cukup set read_at = now untuk yang belum dibaca, dan geser pointer user.
     */
    public static function markAllReadFor(User $user): void
    {
        static::unreadFor($user)->update(['read_at' => now()]);

        $user->forceFill(['notifications_read_at' => now()])->save();
    }
}
