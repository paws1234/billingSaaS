<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Billing\BillingService;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    public function __construct(protected BillingService $billing)
    {
    }

    public function plans()
    {
        return Plan::query()->get();
    }

    public function index(Request $request)
    {
        return $request->user()->subscriptions()->with('plan')->get();
    }

    public function cancel(Request $request)
    {
        $subscriptionId = $request->input('subscription_id');

        $subscription = Subscription::where('id', $subscriptionId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $subscription->status = 'canceled';
        $subscription->ends_at = now();
        $subscription->save();

        // Cancel on Stripe
        if ($subscription->provider === 'stripe' && $subscription->provider_subscription_id) {
            try {
                $stripe = new StripeClient(config('services.stripe.secret'));
                $stripe->subscriptions->cancel($subscription->provider_subscription_id);
            } catch (\Exception $e) {
                \Log::error('Stripe cancellation failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['message' => 'Subscription canceled']);
    }

    public function changePlan(Request $request, Subscription $subscription)
    {
        abort_unless($subscription->user_id === $request->user()->id, 403);

        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $newPlan = Plan::findOrFail($request->plan_id);

        // Cannot change if subscription is not active
        if (!in_array($subscription->status, ['active', 'trialing'])) {
            return response()->json([
                'message' => 'Can only change plan for active subscriptions',
            ], 422);
        }

        // Update on Stripe
        if ($subscription->provider === 'stripe' && $subscription->provider_subscription_id) {
            try {
                $stripe = new StripeClient(config('services.stripe.secret'));
                
                // Get the subscription from Stripe
                $stripeSubscription = $stripe->subscriptions->retrieve($subscription->provider_subscription_id);
                
                // Update the subscription with new price
                $stripe->subscriptions->update($subscription->provider_subscription_id, [
                    'items' => [[
                        'id' => $stripeSubscription->items->data[0]->id,
                        'price' => $newPlan->provider_plan_id,
                    ]],
                    'proration_behavior' => 'create_prorations', // Create prorated invoice
                ]);

                // Update local subscription
                $subscription->plan_id = $newPlan->id;
                $subscription->save();

                return response()->json([
                    'message' => 'Plan changed successfully',
                    'subscription' => $subscription->load('plan'),
                ]);
            } catch (\Exception $e) {
                \Log::error('Plan change failed', ['error' => $e->getMessage()]);
                return response()->json([
                    'message' => 'Failed to change plan: ' . $e->getMessage(),
                ], 500);
            }
        }

        return response()->json(['message' => 'Provider not supported'], 422);
    }
}
