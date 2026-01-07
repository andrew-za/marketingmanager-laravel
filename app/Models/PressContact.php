<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class PressContact extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'phone',
        'company',
        'job_title',
        'media_outlet',
        'type',
        'notes',
        'tags',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(PressDistribution::class);
    }
}

