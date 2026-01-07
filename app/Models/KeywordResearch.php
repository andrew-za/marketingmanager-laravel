<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\HasOrganizationScope;

class KeywordResearch extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $table = 'keyword_research';

    protected $fillable = [
        'organization_id',
        'keyword',
        'search_volume',
        'difficulty',
        'cpc',
        'related_keywords',
        'trends',
    ];

    protected function casts(): array
    {
        return [
            'search_volume' => 'integer',
            'difficulty' => 'integer',
            'cpc' => 'decimal:2',
            'related_keywords' => 'array',
            'trends' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}


