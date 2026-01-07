<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_topic_id',
        'user_id',
        'message',
        'attachments',
        'reply_to',
        'is_edited',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'is_edited' => 'boolean',
            'edited_at' => 'datetime',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ChatTopic::class, 'chat_topic_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'reply_to');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ChatReaction::class, 'chat_message_id');
    }

    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }
}

