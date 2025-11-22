<?php

namespace App\Services\Payments;

use App\Mail\InvoicePaid;
use App\Mail\PaymentFailed;
use App\Mail\SubscriptionActivated;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ReceiptService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\StripeClient;

class StripePaymentService implements PaymentProvider
{
    protected StripeClient $stripe;
    protected ReceiptService $receiptService;

    public function __construct(ReceiptService $receiptService)
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
        $this->receiptService = $receiptService;
    }

    public function createCheckoutSession(User $user, Plan $plan): array
    {
        $customerId = $user->provider_customer_id;

        if (! $customerId) {
            $customer = $this->stripe->customers->create([
                'email' => $user->email,
                'name' => $user->billing_name ?? $user->name,
            ]);

            $customerId = $customer->id;
            $user->provider_customer_id = $customerId;
            $user->save();
        }

        $sessionParams = [
            'mode' => 'subscription',
            'customer' => $customerId,
            'line_items' => [[
                'price' => $plan->provider_plan_id,
                'quantity' => 1,
            ]],
            'success_url' => env('FRONTEND_URL', 'http://localhost:3000') . '/subscriptions?success=true&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => env('FRONTEND_URL', 'http://localhost:3000') . '/plans?canceled=true',
        ];

        // Add trial period if configured (14 days default)
        $trialDays = config('billing.trial_days', 14);
        if ($trialDays > 0 && !$user->subscriptions()->where('status', '!=', 'canceled')->exists()) {
            $sessionParams['subscription_data'] = [
                'trial_period_days' => $trialDays,
            ];
        }

        $session = $this->stripe->checkout->sessions->create($sessionParams);

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'provider' => 'stripe',
            'provider_customer_id' => $customerId,
            'status' => 'pending',
            'trial_ends_at' => $trialDays > 0 ? now()->addDays($trialDays) : null,
        ]);

        return [
            'id' => $session->id,
            'url' => $session->url,
        ];
    }

    public function handleWebhook(array $payload, ?string $signature = null): void
    {
        $type = $payload['type'] ?? null;
        $data = $payload['data']['object'] ?? null;

        if (! $type || ! $data) {
            return;
        }

        switch ($type) {
            case 'checkout.session.completed':
                $this->handleCheckoutCompleted($data);
                break;

            case 'invoice.payment_succeeded':
                $this->handleInvoicePaid($data);
                break;

            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($data);
                break;

            case 'invoice.payment_failed':
                $this->handlePaymentFailed($data);
                break;

            default:
                Log::info('Unhandled Stripe event type', ['type' => $type]);
        }
    }

    protected function handleInvoicePaid(array $invoiceData): void
    {
        $customerId = $invoiceData['customer'] ?? null;
        $subscriptionId = $invoiceData['subscription'] ?? null;

        if (! $customerId) {
            return;
        }

        $user = User::where('provider_customer_id', $customerId)->first();

        if (! $user) {
            return;
        }

        $subscription = null;

        if ($subscriptionId) {
            $subscription = Subscription::where('provider_subscription_id', $subscriptionId)
                ->where('user_id', $user->id)
                ->first();
        }

        if ($subscription) {
            $subscription->status = 'active';
            $subscription->starts_at = now();
            $subscription->save();
        }

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription?->id,
            'provider' => 'stripe',
            'provider_invoice_id' => $invoiceData['id'] ?? null,
            'provider_payment_intent_id' => $invoiceData['payment_intent'] ?? null,
            'amount' => $invoiceData['amount_paid'] ?? 0,
            'currency' => strtoupper($invoiceData['currency'] ?? 'CAD'),
            'status' => 'paid',
            'data' => $invoiceData,
        ]);

        // Generate receipt asynchronously
        if ($invoice) {
            try {
                $this->receiptService->generateAndUpload($invoice);
            } catch (\Exception $e) {
                Log::error('Receipt generation failed', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
            }

            // Send payment confirmation email
            try {
                Mail::to($invoice->user->email)
                    ->send(new InvoicePaid($invoice->load(['subscription.plan', 'user'])));
            } catch (\Exception $e) {
                Log::error('Failed to send invoice paid email', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    protected function handleSubscriptionUpdated(array $subData): void
    {
        $stripeSubId = $subData['id'] ?? null;

        if (! $stripeSubId) {
            return;
        }

        $subscription = Subscription::where('provider_subscription_id', $stripeSubId)->first();

        if (! $subscription) {
            return;
        }

        $statusMap = [
            'active' => 'active',
            'past_due' => 'past_due',
            'canceled' => 'canceled',
            'incomplete' => 'incomplete',
        ];

        $subscription->status = $statusMap[$subData['status']] ?? $subscription->status;
        $subscription->save();
    }

    protected function handleCheckoutCompleted(array $sessionData): void
    {
        $customerId = $sessionData['customer'] ?? null;
        $subscriptionId = $sessionData['subscription'] ?? null;
        $sessionId = $sessionData['id'] ?? null;

        Log::info('Checkout session completed', [
            'session_id' => $sessionId,
            'customer_id' => $customerId,
            'subscription_id' => $subscriptionId
        ]);

        if (! $customerId || ! $subscriptionId) {
            Log::warning('Missing customer or subscription in checkout session');
            return;
        }

        // Find the pending subscription created during checkout
        $subscription = Subscription::where('provider_customer_id', $customerId)
            ->where('status', 'pending')
            ->whereNull('provider_subscription_id')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($subscription) {
            $subscription->provider_subscription_id = $subscriptionId;
            $subscription->status = 'active';
            $subscription->starts_at = now();
            $subscription->save();

            // Send welcome email
            try {
                Mail::to($subscription->user->email)
                    ->send(new SubscriptionActivated($subscription->load('plan')));
            } catch (\Exception $e) {
                Log::error('Failed to send subscription activation email', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Subscription activated via checkout', [
                'subscription_id' => $subscription->id,
                'stripe_subscription_id' => $subscriptionId
            ]);
        } else {
            Log::warning('Could not find pending subscription to activate', [
                'customer_id' => $customerId
            ]);
        }
    }

    protected function handlePaymentFailed(array $invoiceData): void
    {
        $customerId = $invoiceData['customer'] ?? null;
        $subscriptionId = $invoiceData['subscription'] ?? null;

        if (! $customerId) {
            return;
        }

        $user = User::where('provider_customer_id', $customerId)->first();

        if (! $user) {
            return;
        }

        if ($subscriptionId) {
            $subscription = Subscription::where('provider_subscription_id', $subscriptionId)
                ->where('user_id', $user->id)
                ->first();

            if ($subscription) {
                $subscription->status = 'past_due';
                $subscription->save();

                // Send payment failed email
                try {
                    Mail::to($subscription->user->email)
                        ->send(new PaymentFailed($subscription->load(['plan', 'user'])));
                } catch (\Exception $e) {
                    Log::error('Failed to send payment failed email', [
                        'subscription_id' => $subscription->id,
                        'error' => $e->getMessage()
                    ]);
                }

                Log::warning('Payment failed for subscription', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $user->id
                ]);
            }
        }
    }
}
