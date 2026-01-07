<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\HasOrganizationScope;

class DashboardWidget extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'user_id',
        'organization_id',
        'widget_type',
        'title',
        'config',
        'position_x',
        'position_y',
        'width',
        'height',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}


