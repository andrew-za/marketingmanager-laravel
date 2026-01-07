<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailCampaign;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\CampaignRecipient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmailCampaignService
{
    public function createCampaign(array $data, User $user): EmailCampaign
    {
        return DB::transaction(function () use ($data, $user) {
            $campaign = EmailCampaign::create([
                'organization_id' => $user->primaryOrganization()->id,
                'campaign_id' => $data['campaign_id'] ?? null,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'email_template_id' => $data['email_template_id'] ?? null,
                'subject' => $data['subject'],
                'from_name' => $data['from_name'] ?? null,
                'from_email' => $data['from_email'],
                'reply_to_email' => $data['reply_to_email'] ?? null,
                'scheduled_at' => isset($data['scheduled_at']) ? now()->parse($data['scheduled_at']) : null,
                'settings' => $data['settings'] ?? [],
            ]);

            if (isset($data['contact_list_ids']) && is_array($data['contact_list_ids'])) {
                $campaign->contactLists()->attach($data['contact_list_ids']);
                $this->prepareRecipients($campaign);
            }

            return $campaign->load(['contactLists', 'emailTemplate']);
        });
    }

    public function updateCampaign(EmailCampaign $campaign, array $data): EmailCampaign
    {
        return DB::transaction(function () use ($campaign, $data) {
            $campaign->update([
                'name' => $data['name'] ?? $campaign->name,
                'description' => $data['description'] ?? $campaign->description,
                'email_template_id' => $data['email_template_id'] ?? $campaign->email_template_id,
                'subject' => $data['subject'] ?? $campaign->subject,
                'from_name' => $data['from_name'] ?? $campaign->from_name,
                'from_email' => $data['from_email'] ?? $campaign->from_email,
                'reply_to_email' => $data['reply_to_email'] ?? $campaign->reply_to_email,
                'scheduled_at' => isset($data['scheduled_at']) ? now()->parse($data['scheduled_at']) : $campaign->scheduled_at,
                'settings' => $data['settings'] ?? $campaign->settings,
            ]);

            if (isset($data['contact_list_ids']) && is_array($data['contact_list_ids'])) {
                $campaign->contactLists()->sync($data['contact_list_ids']);
                $this->prepareRecipients($campaign);
            }

            return $campaign->load(['contactLists', 'emailTemplate']);
        });
    }

    public function deleteCampaign(EmailCampaign $campaign): bool
    {
        return DB::transaction(function () use ($campaign) {
            $campaign->recipients()->delete();
            $campaign->tracking()->delete();
            return $campaign->delete();
        });
    }

    public function scheduleCampaign(EmailCampaign $campaign, \DateTime $scheduledAt): EmailCampaign
    {
        if (!$campaign->canSend()) {
            throw new \Exception('Campaign cannot be scheduled. Ensure it has recipients.');
        }

        $campaign->update([
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAt,
        ]);

        return $campaign;
    }

    public function prepareRecipients(EmailCampaign $campaign): void
    {
        $contactIds = Contact::whereHas('contactLists', function ($query) use ($campaign) {
            $query->whereIn('contact_lists.id', $campaign->contactLists->pluck('id'));
        })
        ->where('status', 'active')
        ->whereNull('unsubscribed_at')
        ->pluck('id');

        $existingRecipientIds = $campaign->recipients()->pluck('contact_id');

        $newContactIds = $contactIds->diff($existingRecipientIds);

        foreach ($newContactIds as $contactId) {
            CampaignRecipient::create([
                'email_campaign_id' => $campaign->id,
                'contact_id' => $contactId,
                'status' => 'pending',
            ]);
        }

        $campaign->update(['total_recipients' => $contactIds->count()]);
    }

    public function getSegmentedContacts(array $segmentCriteria): \Illuminate\Database\Eloquent\Collection
    {
        $query = Contact::query()
            ->where('organization_id', auth()->user()->primaryOrganization()->id)
            ->where('status', 'active')
            ->whereNull('unsubscribed_at');

        if (isset($segmentCriteria['tags']) && is_array($segmentCriteria['tags'])) {
            $query->whereHas('tags', function ($q) use ($segmentCriteria) {
                $q->whereIn('tag', $segmentCriteria['tags']);
            });
        }

        if (isset($segmentCriteria['contact_list_ids']) && is_array($segmentCriteria['contact_list_ids'])) {
            $query->whereHas('contactLists', function ($q) use ($segmentCriteria) {
                $q->whereIn('contact_lists.id', $segmentCriteria['contact_list_ids']);
            });
        }

        if (isset($segmentCriteria['custom_fields']) && is_array($segmentCriteria['custom_fields'])) {
            foreach ($segmentCriteria['custom_fields'] as $field => $value) {
                $query->whereJsonContains("custom_fields->{$field}", $value);
            }
        }

        return $query->get();
    }
}


