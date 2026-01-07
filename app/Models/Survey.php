<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class Survey extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'title',
        'description',
        'status',
        'start_date',
        'end_date',
        'response_count',
        'distribution_settings',
        'analytics_settings',
        'settings',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'distribution_settings' => 'array',
            'analytics_settings' => 'array',
            'settings' => 'array',
        ];
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(SurveyDistribution::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }
}

