<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
        'logo',
        'status',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'agency_team_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function clients(): HasMany
    {
        return $this->hasMany(AgencyClient::class);
    }

    public function clientOrganizations()
    {
        return $this->belongsToMany(Organization::class, 'agency_clients')
            ->withPivot('status')
            ->withTimestamps();
    }
}


