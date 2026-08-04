<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'profile_photo',
        'phone',
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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin(): bool
    {
        return $this->role?->slug === 'admin';
    }

    public function hasRole(string $roleSlug): bool
    {
        return $this->role?->slug === $roleSlug;
    }

    public function hasPermission(string $module, string $action): bool
    {
        return $this->role?->hasPermission($module, $action) ?? false;
    }

    public function hasModuleAccess(string $module): bool
    {
        return $this->role?->hasModuleAccess($module) ?? false;
    }

    public function can($ability, $arguments = []): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (is_string($ability) && str_contains($ability, '.')) {
            [$module, $action] = explode('.', $ability, 2);
            return $this->hasPermission($module, $action);
        }

        return parent::can($ability, $arguments);
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if ($this->profile_photo) {
            return asset('storage/profile_photos/' . $this->profile_photo);
        }
        return null;
    }
}
