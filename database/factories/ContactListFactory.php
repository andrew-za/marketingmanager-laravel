<?php

namespace Database\Factories;

use App\Models\ContactList;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactListFactory extends Factory
{
    protected $model = ContactList::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'contact_count' => fake()->numberBetween(0, 1000),
            'is_active' => true,
        ];
    }
}

