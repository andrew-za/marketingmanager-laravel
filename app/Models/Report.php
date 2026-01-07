<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class Report extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'type',
        'config',
        'data',
        'file_path',
        'format',
        'schedule',
        'last_generated_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'data' => 'array',
            'schedule' => 'array',
            'last_generated_at' => 'datetime',
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

    public function schedules(): HasMany
    {
        return $this->hasMany(ReportSchedule::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(ReportShare::class);
    }
}

