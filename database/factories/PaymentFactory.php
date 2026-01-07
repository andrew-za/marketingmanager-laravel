<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'payment_method' => fake()->randomElement(['stripe', 'paypal', 'bank_transfer']),
            'transaction_id' => fake()->uuid(),
            'status' => fake()->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'paid_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}

