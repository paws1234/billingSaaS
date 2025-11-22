<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Payments\StripePaymentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function __construct(protected StripePaymentService $service)
    {
    }

    public function handle(Request $request): Response
    {
        $payload = $request->all();
        $signature = $request->header('Stripe-Signature');

        $this->service->handleWebhook($payload, $signature);

        return response('ok', 200);
    }
}
