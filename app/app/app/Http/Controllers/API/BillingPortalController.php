<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class BillingPortalController extends Controller
{
    public function createSession(Request $request)
    {
        $user = $request->user();

        if (!$user->provider_customer_id) {
            return response()->json([
                'message' => 'No billing account found. Please subscribe to a plan first.'
            ], 404);
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            
            $session = $stripe->billingPortal->sessions->create([
                'customer' => $user->provider_customer_id,
                'return_url' => env('FRONTEND_URL', config('app.url')) . '/subscriptions',
            ]);

            return response()->json([
                'portal_url' => $session->url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create billing portal session',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
