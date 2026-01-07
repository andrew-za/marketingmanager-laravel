<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class AiGeneration extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'user_id',
        'type',
        'provider',
        'model',
        'prompt',
        'generated_content',
        'metadata',
        'tokens_used',
        'cost',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'tokens_used' => 'integer',
            'cost' => 'decimal:4',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generatedImages(): HasMany
    {
        return $this->hasMany(GeneratedImage::class);
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(AiUsageLog::class);
    }

    public function markAsCompleted(string $content, int $tokensUsed, float $cost): void
    {
        $this->update([
            'status' => 'completed',
            'generated_content' => $content,
            'tokens_used' => $tokensUsed,
            'cost' => $cost,
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}


