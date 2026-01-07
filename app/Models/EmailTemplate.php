<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Traits\HasOrganizationScope;

class EmailTemplate extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'subject',
        'html_content',
        'text_content',
        'variables',
        'category',
        'is_public',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_public' => 'boolean',
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

    public function emailCampaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class);
    }

    public function render(array $data = []): array
    {
        $htmlContent = $this->html_content;
        $textContent = $this->text_content;
        $subject = $this->subject;

        foreach ($data as $key => $value) {
            $htmlContent = str_replace("{{{$key}}}", $value, $htmlContent);
            $textContent = str_replace("{{{$key}}}", $value, $textContent);
            $subject = str_replace("{{{$key}}}", $value, $subject);
        }

        return [
            'subject' => $subject,
            'html_content' => $htmlContent,
            'text_content' => $textContent,
        ];
    }
}


