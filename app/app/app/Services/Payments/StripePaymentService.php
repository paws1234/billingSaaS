<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ReceiptService;
use Illuminate\Support\Facades\Log;
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
            case 'invoice.payment_succeeded':
                $this->handleInvoicePaid($data);
                break;

            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($data);
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
}
