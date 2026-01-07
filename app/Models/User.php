<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'timezone',
        'status',
        'user_type',
        'email_verified_at',
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

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'user_roles')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function primaryOrganization()
    {
        return $this->organizations()->first();
    }

    public function agencies()
    {
        return $this->belongsToMany(Agency::class, 'agency_team_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function primaryAgency()
    {
        return $this->agencies()->first();
    }

    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isAgency(): bool
    {
        return $this->user_type === 'agency';
    }

    public function isCustomer(): bool
    {
        return $this->user_type === 'customer';
    }

    public function hasAccessToOrganization(int $organizationId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->organizations()->where('organizations.id', $organizationId)->exists();
    }

    public function isAgencyMember(int $agencyId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->agencies()->where('agencies.id', $agencyId)->exists();
    }
}

