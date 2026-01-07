<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Channel;
use App\Models\Organization;
use App\Models\ScheduledPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduledPostFactory extends Factory
{
    protected $model = ScheduledPost::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'campaign_id' => Campaign::factory(),
            'channel_id' => Channel::factory(),
            'content' => fake()->paragraph(),
            'scheduled_at' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => 'scheduled',
            'created_by' => User::factory(),
            'is_recurring' => false,
        ];
    }
}

