<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Client', 'Admin', 'Editor', 'Viewer']),
            'guard_name' => 'web',
            'description' => fake()->sentence(),
            'level' => fake()->numberBetween(1, 10),
        ];
    }
}

