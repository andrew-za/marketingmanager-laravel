<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'template_data',
        'is_public',
        'is_featured',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'template_data' => 'array',
            'is_public' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createCampaignFromTemplate(User $user, int $organizationId, array $overrides = []): Campaign
    {
        $data = array_merge($this->template_data ?? [], $overrides);
        
        return Campaign::create([
            'organization_id' => $organizationId,
            'name' => $data['name'] ?? $this->name,
            'description' => $data['description'] ?? $this->description,
            'status' => 'draft',
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'budget' => $data['budget'] ?? 0,
            'created_by' => $user->id,
        ]);
    }
}


