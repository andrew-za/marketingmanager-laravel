<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->company(),
            'summary' => fake()->paragraph(),
            'audience' => fake()->sentence(),
            'tone_of_voice' => fake()->randomElement(['professional', 'casual', 'friendly', 'formal']),
            'keywords' => implode(',', fake()->words(5)),
            'status' => 'active',
        ];
    }
}

