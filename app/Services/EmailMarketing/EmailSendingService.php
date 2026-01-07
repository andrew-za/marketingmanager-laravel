<?php

namespace App\Services\EmailMarketing;

use App\Models\EmailCampaign;
use App\Models\Contact;
use App\Models\CampaignRecipient;
use App\Models\EmailTracking;
use App\Mail\CampaignEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailSendingService
{
    public function sendCampaign(EmailCampaign $campaign): void
    {
        if (!$campaign->canSend()) {
            throw new \Exception('Campaign cannot be sent.');
        }

        $campaign->markAsSending();

        $recipients = $campaign->recipients()
            ->where('status', 'pending')
            ->with('contact')
            ->get();

        foreach ($recipients as $recipient) {
            if ($recipient->contact->isSubscribed()) {
                $this->sendToRecipient($campaign, $recipient);
            } else {
                $recipient->markAsBounced('Contact is unsubscribed');
            }
        }

        $campaign->markAsSent();
        $campaign->updateMetrics();
    }

    public function sendToRecipient(EmailCampaign $campaign, CampaignRecipient $recipient): void
    {
        try {
            $trackingToken = $this->generateTrackingToken($campaign, $recipient);

            Mail::to($recipient->contact->email)->send(
                new CampaignEmail($campaign, $recipient->contact, $trackingToken)
            );

            $recipient->markAsSent();

            $this->logTracking($campaign, $recipient->contact, 'sent', $trackingToken);

        } catch (\Exception $e) {
            $recipient->markAsBounced($e->getMessage());
            $this->logTracking($campaign, $recipient->contact, 'bounced', null, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function trackOpen(string $trackingToken): void
    {
        $tracking = EmailTracking::where('tracking_token', $trackingToken)->first();

        if ($tracking && $tracking->event_type === 'sent') {
            $recipient = CampaignRecipient::where('email_campaign_id', $tracking->email_campaign_id)
                ->where('contact_id', $tracking->contact_id)
                ->first();

            if ($recipient) {
                $recipient->markAsOpened();
                $tracking->emailCampaign->updateMetrics();
            }

            EmailTracking::create([
                'email_campaign_id' => $tracking->email_campaign_id,
                'contact_id' => $tracking->contact_id,
                'tracking_token' => Str::random(32),
                'event_type' => 'opened',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'occurred_at' => now(),
            ]);

            $this->logContactActivity($tracking->contact, $tracking->emailCampaign, 'opened');
        }
    }

    public function trackClick(string $trackingToken, string $linkUrl): void
    {
        $tracking = EmailTracking::where('tracking_token', $trackingToken)->first();

        if ($tracking) {
            $recipient = CampaignRecipient::where('email_campaign_id', $tracking->email_campaign_id)
                ->where('contact_id', $tracking->contact_id)
                ->first();

            if ($recipient) {
                $recipient->markAsClicked();
                $tracking->emailCampaign->updateMetrics();
            }

            EmailTracking::create([
                'email_campaign_id' => $tracking->email_campaign_id,
                'contact_id' => $tracking->contact_id,
                'tracking_token' => Str::random(32),
                'event_type' => 'clicked',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'link_url' => $linkUrl,
                'occurred_at' => now(),
            ]);

            $this->logContactActivity($tracking->contact, $tracking->emailCampaign, 'clicked', [
                'link_url' => $linkUrl,
            ]);
        }
    }

    public function trackUnsubscribe(string $trackingToken): void
    {
        $tracking = EmailTracking::where('tracking_token', $trackingToken)->first();

        if ($tracking) {
            $recipient = CampaignRecipient::where('email_campaign_id', $tracking->email_campaign_id)
                ->where('contact_id', $tracking->contact_id)
                ->first();

            if ($recipient) {
                $recipient->markAsUnsubscribed();
                $tracking->emailCampaign->updateMetrics();
            }

            EmailTracking::create([
                'email_campaign_id' => $tracking->email_campaign_id,
                'contact_id' => $tracking->contact_id,
                'tracking_token' => Str::random(32),
                'event_type' => 'unsubscribed',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'occurred_at' => now(),
            ]);

            $this->logContactActivity($tracking->contact, $tracking->emailCampaign, 'unsubscribed');
        }
    }

    public function processAbTest(EmailCampaign $campaign, Contact $contact): array
    {
        $settings = $campaign->settings ?? [];
        $defaultContent = $campaign->emailTemplate?->html_content ?? '';
        $defaultSubject = $campaign->subject;
        
        if (!isset($settings['ab_testing']) || !$settings['ab_testing']['enabled']) {
            return [
                'subject' => $defaultSubject,
                'html_content' => $defaultContent,
            ];
        }

        $variants = $settings['ab_testing']['variants'] ?? [];
        if (empty($variants)) {
            return [
                'subject' => $defaultSubject,
                'html_content' => $defaultContent,
            ];
        }

        $variantIndex = $contact->id % count($variants);
        $selectedVariant = $variants[$variantIndex];

        return [
            'subject' => $selectedVariant['subject'] ?? $defaultSubject,
            'html_content' => $selectedVariant['html_content'] ?? $defaultContent,
        ];
    }

    private function generateTrackingToken(EmailCampaign $campaign, CampaignRecipient $recipient): string
    {
        $token = Str::random(32);

        EmailTracking::create([
            'email_campaign_id' => $campaign->id,
            'contact_id' => $recipient->contact_id,
            'tracking_token' => $token,
            'event_type' => 'sent',
            'occurred_at' => now(),
        ]);

        return $token;
    }

    private function logTracking(
        EmailCampaign $campaign,
        Contact $contact,
        string $eventType,
        ?string $trackingToken = null,
        array $metadata = []
    ): void {
        EmailTracking::create([
            'email_campaign_id' => $campaign->id,
            'contact_id' => $contact->id,
            'tracking_token' => $trackingToken ?? Str::random(32),
            'event_type' => $eventType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }

    private function logContactActivity(Contact $contact, EmailCampaign $campaign, string $type, array $metadata = []): void
    {
        \App\Models\ContactActivity::create([
            'contact_id' => $contact->id,
            'email_campaign_id' => $campaign->id,
            'type' => $type,
            'description' => "Email campaign: {$campaign->name} - {$type}",
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }
}


