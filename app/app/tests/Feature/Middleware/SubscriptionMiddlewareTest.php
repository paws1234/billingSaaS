<?php

namespace Tests\Feature\Middleware;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribed_user_can_access_protected_route(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();

        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/projects');

        $response->assertStatus(200);
    }

    public function test_non_subscribed_user_cannot_access_protected_route(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/projects');

        $response->assertStatus(403)
            ->assertJson(['subscription_required' => true]);
    }

    public function test_admin_bypasses_subscription_check(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->getJson('/api/projects');

        $response->assertStatus(200);
    }

    public function test_trialing_user_can_access_protected_route(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();

        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/projects');

        $response->assertStatus(200);
    }
}
