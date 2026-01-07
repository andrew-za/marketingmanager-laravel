<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\HasOrganizationScope;

class ReviewResponse extends Model
{
    use HasFactory, HasOrganizationScope;

    public const TYPE_PUBLIC = 'public';
    public const TYPE_PRIVATE = 'private';

    protected $fillable = [
        'review_id',
        'organization_id',
        'responded_by',
        'response',
        'response_type',
    ];

    protected function casts(): array
    {
        return [
            'response_type' => 'string',
        ];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function isPublic(): bool
    {
        return $this->response_type === self::TYPE_PUBLIC;
    }

    public function isPrivate(): bool
    {
        return $this->response_type === self::TYPE_PRIVATE;
    }
}

