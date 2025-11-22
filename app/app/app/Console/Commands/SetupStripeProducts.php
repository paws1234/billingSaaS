<?php

namespace App\Console\Commands;

use App\Models\Plan;
use Illuminate\Console\Command;
use Stripe\StripeClient;

class SetupStripeProducts extends Command
{
    protected $signature = 'stripe:setup-products';

    protected $description = 'Create Stripe products and prices, then update database';

    public function handle()
    {
        $this->info('Setting up Stripe products...');

        $stripe = new StripeClient(config('services.stripe.secret'));

        // Define plans
        $plans = [
            [
                'name' => 'Basic Plan',
                'slug' => 'basic',
                'amount' => 999, // $9.99 in cents
                'description' => 'Basic features for getting started',
                'features' => ['5 Projects', '1,000 API Calls/month', '3 Team Members'],
            ],
            [
                'name' => 'Pro Plan',
                'slug' => 'pro',
                'amount' => 2999, // $29.99 in cents
                'description' => 'Professional features for growing teams',
                'features' => ['50 Projects', '10,000 API Calls/month', '10 Team Members', 'Advanced Analytics', 'Priority Support'],
            ],
            [
                'name' => 'Enterprise Plan',
                'slug' => 'enterprise',
                'amount' => 9999, // $99.99 in cents
                'description' => 'Enterprise features for large organizations',
                'features' => ['Unlimited Projects', 'Unlimited API Calls', 'Unlimited Team Members', 'Advanced Analytics', 'Priority Support', 'Custom Integration', 'Dedicated Support'],
            ],
        ];

        foreach ($plans as $planData) {
            $this->info("Creating {$planData['name']}...");

            try {
                // Check if product already exists
                $existingPlan = Plan::where('slug', $planData['slug'])->first();

                if ($existingPlan && $existingPlan->provider_plan_id) {
                    // Verify if the price exists in Stripe
                    try {
                        $stripe->prices->retrieve($existingPlan->provider_plan_id);
                        $this->warn("  ✓ {$planData['name']} already exists (Price ID: {$existingPlan->provider_plan_id})");

                        continue;
                    } catch (\Exception $e) {
                        $this->warn('  Existing price not found in Stripe, creating new one...');
                    }
                }

                // Create product in Stripe
                $product = $stripe->products->create([
                    'name' => $planData['name'],
                    'description' => $planData['description'],
                ]);

                $this->info("  ✓ Product created: {$product->id}");

                // Create price in Stripe
                $price = $stripe->prices->create([
                    'product' => $product->id,
                    'unit_amount' => $planData['amount'],
                    'currency' => 'cad',
                    'recurring' => [
                        'interval' => 'month',
                    ],
                ]);

                $this->info("  ✓ Price created: {$price->id}");

                // Update or create plan in database
                Plan::updateOrCreate(
                    ['slug' => $planData['slug']],
                    [
                        'name' => $planData['name'],
                        'provider' => 'stripe',
                        'provider_plan_id' => $price->id,
                        'interval' => 'monthly',
                        'amount' => $planData['amount'],
                        'currency' => 'CAD',
                        'metadata' => [
                            'features' => $planData['features'],
                        ],
                    ]
                );

                $this->info("  ✓ Database updated with Price ID: {$price->id}");
                $this->line('');

            } catch (\Exception $e) {
                $this->error("  ✗ Error creating {$planData['name']}: {$e->getMessage()}");
                $this->line('');
            }
        }

        $this->info('✅ Stripe setup complete!');
        $this->line('');
        $this->info('Your plans:');

        Plan::all()->each(function ($plan) {
            $amount = $plan->amount / 100;
            $this->line("  • {$plan->name} - \${$amount} CAD/month (ID: {$plan->provider_plan_id})");
        });

        return 0;
    }
}
