<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CampaignEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmailCampaign $campaign,
        public Contact $contact,
        public string $trackingToken
    ) {}

    public function build()
    {
        $this->campaign->load('organization');
        
        $emailService = app(\App\Services\EmailMarketing\EmailSendingService::class);
        $content = $emailService->processAbTest($this->campaign, $this->contact);

        $htmlContent = $this->injectTrackingPixel($content['html_content']);
        $htmlContent = $this->injectLinkTracking($htmlContent);

        $mail = $this->subject($content['subject'])
            ->from($this->campaign->from_email, $this->campaign->from_name)
            ->replyTo($this->campaign->reply_to_email ?? $this->campaign->from_email)
            ->view('emails.campaign', [
                'htmlContent' => $htmlContent,
                'campaign' => $this->campaign,
                'contact' => $this->contact,
                'trackingToken' => $this->trackingToken,
            ]);

        if ($this->campaign->emailTemplate?->text_content) {
            $mail->text('emails.campaign-text', [
                'textContent' => $this->campaign->emailTemplate->text_content,
                'campaign' => $this->campaign,
                'contact' => $this->contact,
            ]);
        }

        return $mail;
    }

    private function injectTrackingPixel(string $htmlContent): string
    {
        $trackingUrl = route('email.track.open', ['token' => $this->trackingToken]);
        $pixel = "<img src=\"{$trackingUrl}\" width=\"1\" height=\"1\" style=\"display:none;\" alt=\"\" />";
        
        if (strpos($htmlContent, '</body>') !== false) {
            return str_replace('</body>', $pixel . '</body>', $htmlContent);
        }
        
        return $htmlContent . $pixel;
    }

    private function injectLinkTracking(string $htmlContent): string
    {
        preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $htmlContent, $matches);

        foreach ($matches[1] as $index => $url) {
            if (strpos($url, route('email.track.click')) === false && 
                strpos($url, 'mailto:') === false &&
                strpos($url, 'tel:') === false) {
                $trackingUrl = route('email.track.click', [
                    'token' => $this->trackingToken,
                    'url' => urlencode($url),
                ]);
                $htmlContent = str_replace($url, $trackingUrl, $htmlContent);
            }
        }

        return $htmlContent;
    }
}


