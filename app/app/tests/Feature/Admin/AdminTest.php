<?php

namespace Tests\Feature\Admin;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_subscriptions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        Subscription::factory()->count(5)->create();

        $response = $this->actingAs($admin)
            ->getJson('/api/admin/subscriptions');

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    public function test_regular_user_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->getJson('/api/admin/subscriptions');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_stats(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Subscription::factory()->create(['status' => 'active']);
        Subscription::factory()->create(['status' => 'canceled']);
        Invoice::factory()->create(['status' => 'paid', 'amount' => 1000]);

        $response = $this->actingAs($admin)
            ->getJson('/api/admin/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_users',
                'active_subscriptions',
                'canceled_subscriptions',
                'total_revenue',
            ]);
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $response = $this->getJson('/api/admin/subscriptions');

        $response->assertStatus(401);
    }
}
