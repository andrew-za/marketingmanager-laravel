<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Support\Traits\HasOrganizationScope;

class ContactList extends Model
{
    use HasFactory, HasOrganizationScope;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'contact_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_list_contacts')
            ->withPivot('subscribed_at')
            ->withTimestamps();
    }

    public function emailCampaigns(): BelongsToMany
    {
        return $this->belongsToMany(EmailCampaign::class, 'email_campaign_contact_lists')
            ->withTimestamps();
    }

    public function updateContactCount(): void
    {
        $this->update([
            'contact_count' => $this->contacts()->where('status', 'active')->count(),
        ]);
    }
}


