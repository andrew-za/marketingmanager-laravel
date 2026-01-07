<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'name',
        'type',
        'url',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}


