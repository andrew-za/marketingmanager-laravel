<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\CampaignRepositoryInterface;
use App\Repositories\Eloquent\CampaignRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CampaignRepositoryInterface::class, CampaignRepository::class);
    }

    public function boot(): void
    {
        //
    }
}

