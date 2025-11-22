<?php

namespace App\Services\Payments;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditPaymentService implements PaymentProvider
{
    protected string $secret;

    public function __construct()
    {
        $this->secret = config('services.xendit.secret_key');
    }

    public function createCheckoutSession(User $user, Plan $plan): array
    {
        $response = Http::withBasicAuth($this->secret, '')
            ->post('https://api.xendit.co/v2/invoices', [
                'external_id' => 'plan_' . $plan->id . '_user_' . $user->id,
                'amount' => $plan->amount / 100,
                'payer_email' => $user->email,
                'description' => $plan->name,
            ])
            ->throw()
            ->json();

        return [
            'id' => $response['id'],
            'url' => $response['invoice_url'],
        ];
    }

    public function handleWebhook(array $payload, ?string $signature = null): void
    {
        Log::info('Xendit webhook received', $payload);
        // Implement mapping to Invoice / Subscription similar to Stripe.
    }
}
