<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->whereIn('name', $roles)->isNotEmpty();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles->some->hasPermission($permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return $this->roles->some(function ($role) use ($permissions) {
            return $role->permissions->whereIn('name', $permissions)->isNotEmpty();
        });
    }

    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        return $this->roles->flatMap->permissions->unique('name');
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->roles->pluck('label')->implode(', ') ?: '-';
    }
}
