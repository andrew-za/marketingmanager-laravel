<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'invoice_number' => 'INV-' . fake()->unique()->numerify('#######'),
            'status' => fake()->randomElement(['pending', 'paid', 'overdue', 'cancelled']),
            'subtotal' => fake()->randomFloat(2, 100, 10000),
            'tax' => fake()->randomFloat(2, 10, 1000),
            'total' => fake()->randomFloat(2, 110, 11000),
            'currency' => 'USD',
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
        ];
    }
}

