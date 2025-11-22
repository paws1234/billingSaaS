<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_plans(): void
    {
        Plan::factory()->create([
            'name' => 'Basic Plan',
            'slug' => 'basic',
            'amount' => 999,
            'interval' => 'monthly',
        ]);

        Plan::factory()->create([
            'name' => 'Pro Plan',
            'slug' => 'pro',
            'amount' => 2999,
            'interval' => 'monthly',
        ]);

        $response = $this->getJson('/api/plans');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => 'Basic Plan'])
            ->assertJsonFragment(['name' => 'Pro Plan']);
    }

    public function test_plan_has_correct_structure(): void
    {
        Plan::factory()->create([
            'slug' => 'basic',
            'metadata' => ['features' => ['Feature 1', 'Feature 2']],
        ]);

        $response = $this->getJson('/api/plans');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'provider',
                    'provider_plan_id',
                    'interval',
                    'amount',
                    'currency',
                    'metadata',
                ]
            ]);
    }
}
