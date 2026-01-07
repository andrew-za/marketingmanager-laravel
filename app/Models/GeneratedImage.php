<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\HasOrganizationScope;

class GeneratedImage extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'user_id',
        'ai_generation_id',
        'provider',
        'model',
        'prompt',
        'image_url',
        'image_path',
        'metadata',
        'width',
        'height',
        'cost',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'width' => 'integer',
            'height' => 'integer',
            'cost' => 'decimal:4',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiGeneration(): BelongsTo
    {
        return $this->belongsTo(AiGeneration::class);
    }
}


