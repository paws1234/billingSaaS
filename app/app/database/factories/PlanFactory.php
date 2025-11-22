<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true).' Plan',
            'slug' => fake()->unique()->slug(2),
            'provider' => 'stripe',
            'provider_plan_id' => 'price_'.fake()->unique()->bothify('??????????'),
            'interval' => 'monthly',
            'amount' => fake()->numberBetween(500, 10000),
            'currency' => 'CAD',
            'metadata' => [
                'features' => [
                    fake()->sentence(3),
                    fake()->sentence(3),
                    fake()->sentence(3),
                ],
            ],
        ];
    }
}
