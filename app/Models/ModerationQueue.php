<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModerationQueue extends Model
{
    use HasFactory;

    protected $table = 'moderation_queue';

    protected $fillable = [
        'moderatable_type',
        'moderatable_id',
        'type',
        'status',
        'flagged_by',
        'reviewed_by',
        'reason',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function moderatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function flaggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'flagged_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}

