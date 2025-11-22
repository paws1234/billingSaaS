<?php

namespace Tests\Feature\Middleware;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanFeatureMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_pro_user_can_access_analytics(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create([
            'slug' => 'pro',
            'metadata' => ['features' => ['advanced_analytics']],
        ]);

        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/analytics');

        $response->assertStatus(200);
    }

    public function test_basic_user_cannot_access_analytics(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create([
            'slug' => 'basic',
            'metadata' => ['features' => []],
        ]);

        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/analytics');

        $response->assertStatus(403)
            ->assertJson(['upgrade_required' => true]);
    }

    public function test_enterprise_user_can_access_priority_support(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create([
            'slug' => 'enterprise',
            'metadata' => ['features' => ['priority_support']],
        ]);

        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/support/priority');

        $response->assertStatus(200);
    }
}
