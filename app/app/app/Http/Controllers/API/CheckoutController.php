<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\Billing\BillingService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(protected BillingService $billing) {}

    public function createSession(Request $request, Plan $plan)
    {
        $session = $this->billing->startCheckout($request->user(), $plan);

        return response()->json([
            'checkout_session_id' => $session['id'],
            'checkout_url' => $session['url'],
        ]);
    }
}
