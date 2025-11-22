<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanFeature
{
    /**
     * Feature limits per plan
     */
    protected array $planLimits = [
        'basic' => [
            'max_projects' => 5,
            'max_api_calls_per_month' => 1000,
            'max_team_members' => 3,
            'advanced_analytics' => false,
            'priority_support' => false,
        ],
        'pro' => [
            'max_projects' => 50,
            'max_api_calls_per_month' => 10000,
            'max_team_members' => 10,
            'advanced_analytics' => true,
            'priority_support' => true,
        ],
        'enterprise' => [
            'max_projects' => -1, // unlimited
            'max_api_calls_per_month' => -1, // unlimited
            'max_team_members' => -1, // unlimited
            'advanced_analytics' => true,
            'priority_support' => true,
        ],
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Admin bypass
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Get user's active subscription
        $subscription = $user->subscriptions()
            ->with('plan')
            ->whereIn('status', ['active', 'trialing'])
            ->first();

        if (! $subscription) {
            return response()->json([
                'message' => 'Active subscription required',
                'subscription_required' => true,
            ], 403);
        }

        $plan = $subscription->plan;
        $planSlug = $plan->slug;

        // Check if plan has access to this feature
        if (! isset($this->planLimits[$planSlug])) {
            return response()->json(['message' => 'Invalid plan'], 500);
        }

        $limits = $this->planLimits[$planSlug];

        // Boolean feature check (e.g., advanced_analytics)
        if (isset($limits[$feature]) && $limits[$feature] === false) {
            return response()->json([
                'message' => 'This feature requires a higher plan tier',
                'required_plan' => 'pro',
                'current_plan' => $planSlug,
                'feature' => $feature,
                'upgrade_required' => true,
            ], 403);
        }

        // Attach plan limits to request for use in controllers
        $request->merge(['plan_limits' => $limits]);

        return $next($request);
    }
}
