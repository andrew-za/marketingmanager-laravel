<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class Chatbot extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'welcome_message',
        'training_data',
        'conversation_flow',
        'supported_languages',
        'default_language',
        'brand_information',
        'analytics_settings',
        'settings',
        'is_active',
        'embed_code',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'training_data' => 'array',
            'conversation_flow' => 'array',
            'supported_languages' => 'array',
            'brand_information' => 'array',
            'analytics_settings' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
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

    public function conversations(): HasMany
    {
        return $this->hasMany(ChatbotConversation::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(ChatbotLead::class);
    }
}

