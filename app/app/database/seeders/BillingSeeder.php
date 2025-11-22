<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create regular user
        User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'billing_name' => 'Test User',
            'billing_address' => '123 Main St',
            'billing_city' => 'Toronto',
            'billing_country' => 'Canada',
            'billing_postal_code' => 'M5H 2N2',
        ]);

        // Create plans
        Plan::create([
            'name' => 'Basic Plan',
            'slug' => 'basic',
            'provider' => 'stripe',
            'provider_plan_id' => 'price_basic_monthly',
            'interval' => 'monthly',
            'amount' => 999, // $9.99 in cents
            'currency' => 'CAD',
            'metadata' => [
                'features' => ['Feature 1', 'Feature 2', 'Feature 3'],
            ],
        ]);

        Plan::create([
            'name' => 'Pro Plan',
            'slug' => 'pro',
            'provider' => 'stripe',
            'provider_plan_id' => 'price_pro_monthly',
            'interval' => 'monthly',
            'amount' => 2999, // $29.99 in cents
            'currency' => 'CAD',
            'metadata' => [
                'features' => ['All Basic features', 'Priority Support', 'Advanced Analytics'],
            ],
        ]);

        Plan::create([
            'name' => 'Enterprise Plan',
            'slug' => 'enterprise',
            'provider' => 'stripe',
            'provider_plan_id' => 'price_enterprise_monthly',
            'interval' => 'monthly',
            'amount' => 9999, // $99.99 in cents
            'currency' => 'CAD',
            'metadata' => [
                'features' => ['All Pro features', 'Custom Integration', 'Dedicated Support'],
            ],
        ]);
    }
}
