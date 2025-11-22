<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Payments\XenditPaymentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XenditWebhookController extends Controller
{
    public function __construct(protected XenditPaymentService $service)
    {
    }

    public function handle(Request $request): Response
    {
        $payload = $request->all();
        $this->service->handleWebhook($payload, null);

        return response('ok', 200);
    }
}
