<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReviewSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'url_template',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'platform', 'slug');
    }
}

