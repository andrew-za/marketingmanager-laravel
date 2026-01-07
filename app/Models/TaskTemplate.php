<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\HasOrganizationScope;

class TaskTemplate extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'task_description',
        'priority',
        'estimated_hours',
        'checklist',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'checklist' => 'array',
            'is_public' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function createTaskFromTemplate(array $additionalData = []): Task
    {
        return Task::create(array_merge([
            'organization_id' => $this->organization_id,
            'title' => $this->name,
            'description' => $this->task_description ?? $this->description,
            'priority' => $this->priority,
            'status' => 'todo',
        ], $additionalData));
    }
}

