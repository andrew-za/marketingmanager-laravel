<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChannelFactory extends Factory
{
    protected $model = Channel::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(2, true),
            'type' => fake()->randomElement(['social_media', 'email', 'whatsapp', 'amplify', 'paid_ads', 'press_release', 'influencer']),
            'platform' => fake()->randomElement(['facebook', 'instagram', 'twitter', 'linkedin', 'email']),
            'status' => 'active',
        ];
    }
}

