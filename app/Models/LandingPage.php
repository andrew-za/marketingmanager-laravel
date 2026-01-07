<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class LandingPage extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'slug',
        'custom_domain',
        'seo_settings',
        'html_content',
        'page_data',
        'template_data',
        'status',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'page_data' => 'array',
            'template_data' => 'array',
            'seo_settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(LandingPageVariant::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(PageAnalytics::class);
    }
}

