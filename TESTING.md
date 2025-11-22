# Billing System Test Guide

## Setup Complete ✅
- Migrations run successfully
- Test data seeded:
  - **Admin**: admin@example.com / password
  - **User**: user@example.com / password
  - **Plans**: Basic ($9.99), Pro ($29.99), Enterprise ($99.99)

## Testing the API

### 1. Install Laravel Sanctum (for API authentication)
```bash
docker compose exec app php artisan vendor:publish --provider="Laravel\Sanctum\ServiceProvider"
docker compose exec app php artisan migrate
```

### 2. Test Endpoints with cURL

#### Get all plans (no auth required)
```bash
curl http://localhost:8000/api/plans
```

#### Login and get token
First, you need to add a login endpoint. Create this file:

**routes/api.php** - Add before the auth:sanctum middleware group:
```php
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user || !\Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json(['token' => $token, 'user' => $user]);
});
```

#### Then test login:
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'
```

#### Get user profile (with token):
```bash
curl http://localhost:8000/api/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Get user subscriptions:
```bash
curl http://localhost:8000/api/subscriptions \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Get user invoices:
```bash
curl http://localhost:8000/api/invoices \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 3. Test Admin Dashboard

#### Visit admin dashboard:
```
http://localhost:8000/admin
```

### 4. Test with Postman/Insomnia

Import these endpoints:
- GET http://localhost:8000/api/plans
- POST http://localhost:8000/api/login
- GET http://localhost:8000/api/me
- GET http://localhost:8000/api/subscriptions
- POST http://localhost:8000/api/subscriptions/cancel
- GET http://localhost:8000/api/invoices
- POST http://localhost:8000/api/checkout/{slug}

### 5. Check Database Tables

```bash
docker compose exec app php artisan tinker
```

Then in tinker:
```php
\App\Models\User::count();
\App\Models\Plan::all();
\App\Models\Subscription::with('user', 'plan')->get();
\App\Models\Invoice::with('user')->get();
```

### 6. Test Webhooks (Stripe)

Webhook endpoints are ready at:
- POST http://localhost:8000/webhook/stripe
- POST http://localhost:8000/webhook/xendit

You'll need to configure these in your Stripe/Xendit dashboard.

## Next Steps

1. Install Sanctum for API authentication
2. Add actual Stripe/Xendit credentials to .env
3. Create Stripe products and prices
4. Test the checkout flow
5. Set up webhook endpoints in provider dashboards
