<?php

namespace Database\Factories;

use App\Models\EmailCampaign;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailCampaignFactory extends Factory
{
    protected $model = EmailCampaign::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'subject' => fake()->sentence(),
            'from_name' => fake()->name(),
            'from_email' => fake()->safeEmail(),
            'status' => 'draft',
            'total_recipients' => fake()->numberBetween(10, 1000),
            'sent_count' => 0,
        ];
    }
}

