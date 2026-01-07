<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Organization;
use App\Models\User;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class ProcessAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Organization $organization,
        private User $user,
        private Campaign $campaign,
        private ?Carbon $startDate = null,
        private ?Carbon $endDate = null
    ) {}

    public function handle(AnalyticsService $analyticsService): void
    {
        $analyticsService->analyzeCampaign(
            $this->organization,
            $this->user,
            $this->campaign,
            $this->startDate,
            $this->endDate
        );
    }
}

