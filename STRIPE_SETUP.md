# Stripe Checkout Setup Guide

## ✅ Step 1: Stripe Keys (DONE)
Your Stripe test keys have been added to `.env`:
- **Secret Key**: `sk_test_51SWDjQ...` ✓
- **Publishable Key**: `pk_test_51SWDjQ...` ✓

## 📦 Step 2: Create Stripe Products & Prices

You need to create 3 subscription products in your Stripe Dashboard:

### Option A: Using Stripe Dashboard (Easiest)

1. **Login to Stripe Dashboard**: https://dashboard.stripe.com/test/products
2. **Create Basic Plan**:
   - Click "**+ Add product**"
   - Product name: `Basic Plan`
   - Description: `Basic features for getting started`
   - Pricing model: **Standard pricing**
   - Price: `$9.99`
   - Billing period: **Monthly**
   - Click "**Save product**"
   - Copy the **Price ID** (starts with `price_...`)

3. **Create Pro Plan**:
   - Product name: `Pro Plan`
   - Price: `$29.99`
   - Billing period: **Monthly**
   - Copy the **Price ID**

4. **Create Enterprise Plan**:
   - Product name: `Enterprise Plan`
   - Price: `$99.99`
   - Billing period: **Monthly**
   - Copy the **Price ID**

### Option B: Using Stripe CLI (Fast)

```bash
# Install Stripe CLI: https://stripe.com/docs/stripe-cli
stripe login

# Create Basic Plan
stripe products create --name="Basic Plan" --description="Basic features"
stripe prices create --product=prod_XXXXX --unit-amount=999 --currency=cad --recurring[interval]=month

# Create Pro Plan
stripe products create --name="Pro Plan" --description="Professional features"
stripe prices create --product=prod_XXXXX --unit-amount=2999 --currency=cad --recurring[interval]=month

# Create Enterprise Plan
stripe products create --name="Enterprise Plan" --description="Enterprise features"
stripe prices create --product=prod_XXXXX --unit-amount=9999 --currency=cad --recurring[interval]=month
```

## 🔧 Step 3: Update Database Seeder

Once you have the 3 **Price IDs** from Stripe, update `app/app/database/seeders/BillingSeeder.php`:

```php
// Line 40-48: Replace with YOUR Price IDs
Plan::updateOrCreate(
    ['slug' => 'basic'],
    [
        'name' => 'Basic Plan',
        'provider' => 'stripe',
        'provider_plan_id' => 'price_YOUR_BASIC_PRICE_ID',  // ← Change this
        'interval' => 'monthly',
        'amount' => 999,
        'currency' => 'CAD',
        // ...
    ]
);

// Line 52-60: Update Pro Plan
'provider_plan_id' => 'price_YOUR_PRO_PRICE_ID',  // ← Change this

// Line 64-72: Update Enterprise Plan
'provider_plan_id' => 'price_YOUR_ENTERPRISE_PRICE_ID',  // ← Change this
```

## 🗄️ Step 4: Update Database

After updating the seeder with your Price IDs:

```bash
cd app/app
php artisan db:seed --class=BillingSeeder
```

Or on Render, the seeder will run automatically on next deployment.

## 🔔 Step 5: Setup Webhook (Important!)

Webhooks allow Stripe to notify your app when payments succeed:

### Production Webhook (Render):

1. Go to: https://dashboard.stripe.com/test/webhooks
2. Click "**+ Add endpoint**"
3. Endpoint URL: `https://billingsaas.onrender.com/webhook/stripe`
4. Events to send:
   - `invoice.payment_succeeded`
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
5. Click "**Add endpoint**"
6. Copy the **Signing secret** (starts with `whsec_...`)
7. Update `.env` in Render:
   - Go to Render dashboard → Environment
   - Add `STRIPE_WEBHOOK_SECRET=whsec_YOUR_SECRET`

### Local Testing Webhook (Optional):

```bash
# Forward Stripe events to your local server
stripe listen --forward-to http://localhost:8000/webhook/stripe
```

## 🚀 Step 6: Deploy to Render

```bash
git add -A
git commit -m "Configure Stripe checkout with real API keys"
git push origin main
```

Render will automatically:
- Deploy with new Stripe keys
- Run database seeder with your Price IDs
- Start accepting real test payments

## 🧪 Step 7: Test Checkout Flow

1. Visit: https://billing-saa-s.vercel.app
2. Click "**Register**" and create an account
3. Go to "**Plans**" and click "Subscribe to Basic" (or any plan)
4. You'll be redirected to **Stripe Checkout**
5. Use test card: `4242 4242 4242 4242`
   - Expiry: Any future date (e.g., `12/34`)
   - CVC: Any 3 digits (e.g., `123`)
   - ZIP: Any 5 digits (e.g., `12345`)
6. Complete payment
7. You'll be redirected back to `/subscriptions` with success message

## 🎯 What Happens When User Subscribes:

1. **User clicks "Subscribe"** → Frontend calls `/api/checkout/{plan}`
2. **Backend creates Stripe Customer** (if first time)
3. **Backend creates Checkout Session** with your Price ID
4. **User redirected to Stripe** → Enters payment info
5. **Stripe processes payment** → Sends webhook to your app
6. **Your webhook handler** → Updates subscription to "active"
7. **User redirected back** → Can now access protected features

## 💳 Test Cards

Stripe provides these test cards (no real money):

- **Success**: `4242 4242 4242 4242`
- **Requires 3D Secure**: `4000 0027 6000 3184`
- **Declined**: `4000 0000 0000 0002`
- **Insufficient Funds**: `4000 0000 0000 9995`

Full list: https://stripe.com/docs/testing#cards

## 📊 Monitor Payments

View all test transactions:
- **Payments**: https://dashboard.stripe.com/test/payments
- **Subscriptions**: https://dashboard.stripe.com/test/subscriptions
- **Customers**: https://dashboard.stripe.com/test/customers
- **Logs**: https://dashboard.stripe.com/test/logs

## 🔒 Going Live (Future)

When ready for production:
1. Activate your Stripe account (requires business verification)
2. Switch to **Live mode** in Stripe dashboard
3. Create live products/prices (same as test)
4. Update `.env` with **live keys** (start with `sk_live_` and `pk_live_`)
5. Update webhook endpoint to use live keys
6. Set `APP_ENV=production` and `APP_DEBUG=false`

## ✅ Current Status

- ✅ Stripe keys configured
- ✅ Checkout URLs point to frontend
- ✅ Webhook endpoint ready (`/webhook/stripe`)
- ⏳ **Next**: Create 3 products in Stripe Dashboard
- ⏳ **Then**: Update seeder with Price IDs
- ⏳ **Finally**: Deploy and test

## 🆘 Troubleshooting

**"No such price"** error:
- Make sure you created the products in **Test mode**
- Verify the Price IDs in your seeder match Stripe

**Payment succeeds but subscription stays "pending"**:
- Check webhook is configured
- Verify `STRIPE_WEBHOOK_SECRET` is set
- Check Render logs for webhook errors

**Checkout redirects to wrong URL**:
- Verify `FRONTEND_URL=https://billing-saa-s.vercel.app` in `.env`
- Check `success_url` and `cancel_url` in `StripePaymentService.php`
