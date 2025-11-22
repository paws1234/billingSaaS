<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subscription_id' => Subscription::factory(),
            'provider' => 'stripe',
            'provider_invoice_id' => 'in_'.fake()->unique()->bothify('??????????'),
            'provider_payment_intent_id' => 'pi_'.fake()->unique()->bothify('??????????'),
            'amount' => fake()->numberBetween(500, 10000),
            'currency' => 'CAD',
            'status' => 'paid',
            'receipt_path' => null,
            'data' => [],
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }
}
