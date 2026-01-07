<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class ChatTopic extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'type',
        'is_private',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
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

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ChatParticipant::class);
    }

    public function latestMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'id', 'chat_topic_id')
            ->latestOfMany();
    }

    public function unreadCountForUser(?int $userId): int
    {
        if (!$userId) {
            return 0;
        }

        $participant = $this->participants()->where('user_id', $userId)->first();
        if (!$participant || !$participant->last_read_at) {
            return $this->messages()->count();
        }

        return $this->messages()
            ->where('created_at', '>', $participant->last_read_at)
            ->count();
    }
}

