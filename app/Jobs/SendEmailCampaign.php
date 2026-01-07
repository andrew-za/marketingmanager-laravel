<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Services\EmailMarketing\EmailSendingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmailCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public EmailCampaign $emailCampaign
    ) {}

    public function handle(EmailSendingService $emailSendingService): void
    {
        try {
            $emailSendingService->sendCampaign($this->emailCampaign);
            Log::info("Email campaign {$this->emailCampaign->id} sent successfully");
        } catch (\Exception $e) {
            Log::error("Failed to send email campaign {$this->emailCampaign->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Job failed after {$this->tries} attempts for email campaign {$this->emailCampaign->id}: " . $exception->getMessage());
        
        $this->emailCampaign->update([
            'status' => 'cancelled',
        ]);
    }
}


