<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatbotConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'chatbot_id',
        'session_id',
        'visitor_name',
        'visitor_email',
        'ip_address',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(ChatbotLead::class, 'conversation_id');
    }
}

