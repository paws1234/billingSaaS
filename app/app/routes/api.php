<?php

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\BillingPortalController;
use App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\API\InvoiceController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\SubscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
    ]);

    $user = \App\Models\User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => 'user',
    ]);

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user->only(['id', 'name', 'email', 'role']),
    ], 201);
});

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user->only(['id', 'name', 'email', 'role']),
    ]);
});

Route::get('/plans', [SubscriptionController::class, 'plans']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [ProfileController::class, 'me']);

    Route::post('/checkout/{plan:slug}', [CheckoutController::class, 'createSession']);

    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/subscriptions/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('/subscriptions/{subscription}/change-plan', [SubscriptionController::class, 'changePlan']);
    Route::post('/subscriptions/{subscription}/resume', [SubscriptionController::class, 'resume']);

    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download']);

    // Billing Portal (Stripe Customer Portal for payment method updates)
    Route::post('/billing/portal', [BillingPortalController::class, 'createSession']);

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/subscriptions', [AdminController::class, 'subscriptions']);
        Route::get('/invoices', [AdminController::class, 'invoices']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/stats', [AdminController::class, 'stats']);
    });

    // Protected features - require active subscription
    Route::middleware('subscribed')->group(function () {
        // Basic features - all plans
        Route::get('/projects', function (Request $request) {
            $limits = $request->get('plan_limits', []);
            return response()->json([
                'message' => 'Projects endpoint',
                'max_projects' => $limits['max_projects'] ?? 0,
                'projects' => [], // Would fetch actual projects
            ]);
        });

        // Advanced features - Pro and Enterprise only
        Route::middleware('plan.feature:advanced_analytics')->group(function () {
            Route::get('/analytics', function (Request $request) {
                return response()->json([
                    'message' => 'Advanced analytics - Pro/Enterprise only',
                    'data' => [
                        'revenue_chart' => [],
                        'user_retention' => [],
                        'conversion_funnel' => [],
                    ],
                ]);
            });
        });

        // Priority support - Pro and Enterprise only
        Route::middleware('plan.feature:priority_support')->group(function () {
            Route::post('/support/priority', function (Request $request) {
                return response()->json([
                    'message' => 'Priority support ticket created',
                    'ticket_id' => rand(1000, 9999),
                    'estimated_response' => '< 1 hour',
                ]);
            });
        });
    });
});
