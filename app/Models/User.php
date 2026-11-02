<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function shortUrls(): HasMany
    {
        return $this->hasMany(ShortUrl::class);
    }

    public function createdInvitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'created_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->name === 'SuperAdmin';
    }

    public function isAdmin(): bool
    {
        return $this->role?->name === 'Admin';
    }

    public function isMember(): bool
    {
        return $this->role?->name === 'Member';
    }
}
