<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\HasOrganizationScope;

class LandingPageTemplate extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'template_content',
        'template_data',
        'category',
        'preview_images',
        'is_public',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'template_data' => 'array',
            'preview_images' => 'array',
            'is_public' => 'boolean',
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
}

