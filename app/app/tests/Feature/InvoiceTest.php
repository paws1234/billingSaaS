<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_invoices(): void
    {
        $user = User::factory()->create();

        Invoice::factory()->create([
            'user_id' => $user->id,
            'amount' => 999,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/invoices');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['status' => 'paid']);
    }

    public function test_user_cannot_view_other_users_invoices(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Invoice::factory()->create([
            'user_id' => $user2->id,
            'amount' => 999,
        ]);

        $response = $this->actingAs($user1)
            ->getJson('/api/invoices');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_guest_cannot_view_invoices(): void
    {
        $response = $this->getJson('/api/invoices');

        $response->assertStatus(401);
    }
}
