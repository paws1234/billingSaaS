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
        // Create admin user (or update if exists)
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Create regular user (or update if exists)
        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'billing_name' => 'Test User',
                'billing_address' => '123 Main St',
                'billing_city' => 'Toronto',
                'billing_country' => 'CA',
                'billing_postal_code' => 'M5H 2N2',
            ]
        );

        // Create plans (or update if exists)
        Plan::updateOrCreate(
            ['slug' => 'basic'],
            [
                'name' => 'Basic Plan',
                'provider' => 'stripe',
                'provider_plan_id' => 'price_basic_monthly',
                'interval' => 'monthly',
                'amount' => 999, // $9.99 in cents
                'currency' => 'CAD',
                'metadata' => [
                    'features' => ['Feature 1', 'Feature 2', 'Feature 3'],
                ],
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro Plan',
                'provider' => 'stripe',
                'provider_plan_id' => 'price_pro_monthly',
                'interval' => 'monthly',
                'amount' => 2999, // $29.99 in cents
                'currency' => 'CAD',
                'metadata' => [
                    'features' => ['All Basic features', 'Priority Support', 'Advanced Analytics'],
                ],
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise Plan',
                'provider' => 'stripe',
                'provider_plan_id' => 'price_enterprise_monthly',
                'interval' => 'monthly',
                'amount' => 9999, // $99.99 in cents
                'currency' => 'CAD',
                'metadata' => [
                    'features' => ['All Pro features', 'Custom Integration', 'Dedicated Support'],
                ],
            ]
        );
    }
}
