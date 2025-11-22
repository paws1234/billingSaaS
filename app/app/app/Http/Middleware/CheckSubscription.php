<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Admin bypass
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Check if user has an active subscription
        $hasActiveSubscription = $user->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->exists();

        if (!$hasActiveSubscription) {
            return response()->json([
                'message' => 'Active subscription required to access this feature',
                'subscription_required' => true
            ], 403);
        }

        return $next($request);
    }
}
