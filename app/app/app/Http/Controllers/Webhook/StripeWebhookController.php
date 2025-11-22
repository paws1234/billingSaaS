<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Payments\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function __construct(protected StripePaymentService $service) {}

    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        // Verify webhook signature if webhook secret is configured
        if ($webhookSecret && $webhookSecret !== 'whsec_xxx') {
            try {
                $event = \Stripe\Webhook::constructEvent(
                    $payload,
                    $signature,
                    $webhookSecret
                );
                $this->service->handleWebhook($event->toArray(), $signature);
            } catch (\UnexpectedValueException $e) {
                Log::error('Invalid webhook payload', ['error' => $e->getMessage()]);

                return response('Invalid payload', 400);
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                Log::error('Invalid webhook signature', ['error' => $e->getMessage()]);

                return response('Invalid signature', 400);
            }
        } else {
            // Fallback for development (webhook secret not configured)
            Log::warning('Webhook signature verification skipped - webhook secret not configured');
            $this->service->handleWebhook(json_decode($payload, true), $signature);
        }

        return response('ok', 200);
    }
}
