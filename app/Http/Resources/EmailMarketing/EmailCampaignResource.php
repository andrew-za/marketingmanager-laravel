<?php

namespace App\Http\Resources\EmailMarketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailCampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'campaign_id' => $this->campaign_id,
            'email_template_id' => $this->email_template_id,
            'status' => $this->status,
            'subject' => $this->subject,
            'from_name' => $this->from_name,
            'from_email' => $this->from_email,
            'reply_to_email' => $this->reply_to_email,
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'sent_at' => $this->sent_at?->toISOString(),
            'total_recipients' => $this->total_recipients,
            'sent_count' => $this->sent_count,
            'delivered_count' => $this->delivered_count,
            'opened_count' => $this->opened_count,
            'clicked_count' => $this->clicked_count,
            'bounced_count' => $this->bounced_count,
            'unsubscribed_count' => $this->unsubscribed_count,
            'settings' => $this->settings,
            'email_template' => $this->whenLoaded('emailTemplate', function () {
                return new EmailTemplateResource($this->emailTemplate);
            }),
            'contact_lists' => $this->whenLoaded('contactLists', function () {
                return ContactListResource::collection($this->contactLists);
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

