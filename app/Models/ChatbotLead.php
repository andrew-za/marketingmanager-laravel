<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'chatbot_id',
        'conversation_id',
        'name',
        'email',
        'phone',
        'message',
        'custom_fields',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
        ];
    }

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatbotConversation::class);
    }
}

