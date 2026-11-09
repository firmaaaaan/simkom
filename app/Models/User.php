<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasUuids, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Alias kompatibilitas: signature lama yang dipakai seluruh blade/controller.
     * checkPermissionTo tidak melempar exception bila permission tidak dikenal.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->checkPermissionTo($permission);
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->roles->pluck('label')->implode(', ') ?: '-';
    }

    /**
     * Kirim notifikasi atur ulang kata sandi dalam bahasa Indonesia.
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
