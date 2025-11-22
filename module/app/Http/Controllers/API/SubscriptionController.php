<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
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

        return response()->json(['message' => 'Subscription canceled']);
    }
}
