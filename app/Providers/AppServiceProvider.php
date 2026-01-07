<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Repositories\Contracts\CampaignRepositoryInterface;
use App\Repositories\Eloquent\CampaignRepository;
use App\Models\ContentApproval;
use App\Models\TaskTemplate;
use App\Models\ProjectTemplate;
use App\Models\User;
use App\Policies\ReviewPolicy;
use App\Policies\TaskTemplatePolicy;
use App\Policies\ProjectTemplatePolicy;
use App\Policies\AdminUserPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        ContentApproval::class => ReviewPolicy::class,
        TaskTemplate::class => TaskTemplatePolicy::class,
        ProjectTemplate::class => ProjectTemplatePolicy::class,
        User::class => AdminUserPolicy::class,
    ];

    public function register(): void
    {
        $this->app->bind(CampaignRepositoryInterface::class, CampaignRepository::class);
    }

    public function boot(): void
    {
        // Register policy mappings
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}


