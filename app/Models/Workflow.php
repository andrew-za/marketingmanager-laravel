<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class Workflow extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'type',
        'steps',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'steps' => 'array',
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

    public function executions(): HasMany
    {
        return $this->hasMany(WorkflowExecution::class);
    }

    public function triggers(): HasMany
    {
        return $this->hasMany(WorkflowTrigger::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowAction::class);
    }
}

