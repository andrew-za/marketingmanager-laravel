<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\Organization;
use App\Models\SocialConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

class SocialConnectionFactory extends Factory
{
    protected $model = SocialConnection::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'channel_id' => Channel::factory(),
            'platform' => fake()->randomElement(['facebook', 'instagram', 'twitter', 'linkedin', 'pinterest', 'tiktok']),
            'account_id' => fake()->uuid(),
            'account_name' => fake()->userName(),
            'account_type' => fake()->randomElement(['page', 'profile', 'business']),
            'status' => 'connected',
            'last_sync_at' => now(),
        ];
    }
}

