# Live Demo Deployment Guide

Deploy your Laravel Billing System to production:
- **Backend API**: Render (free tier)
- **Frontend**: Vercel (free tier)
- **Database**: Render PostgreSQL or Railway MySQL (free tier)

## Prerequisites

- GitHub account
- Render account (https://render.com - sign up free)
- Vercel account (https://vercel.com - sign up free)
- Stripe account (for payment processing)

---

## Part 1: Push to GitHub (Easiest Method!)

### Step 1: Create GitHub Repository

**Via GitHub Website:**
1. Go to https://github.com/new
2. Repository name: `laravel-billing-system` (or any name)
3. Set to **Public** (required for free Vercel/Render)
4. Don't initialize with README (we already have files)
5. Click **Create repository**

### Step 2: Push Your Code

```powershell
cd c:\Users\Paws\Downloads\billing_docker_full

# Initialize git (if not already)
git init

# Add all files
git add .
git commit -m "Initial commit - Laravel Billing System"

# Add GitHub as remote (replace YOUR_USERNAME)
git remote add origin https://github.com/YOUR_USERNAME/laravel-billing-system.git
git branch -M main

# Push to GitHub
git push -u origin main
```

**That's it!** Your code is now on GitHub and ready for deployment.

### Step 2: Update Laravel for Production

Create `app/.env.production` for Render:

```env
APP_NAME="Billing System"
APP_ENV=production
APP_KEY=base64:YOUR_KEY_WILL_BE_GENERATED
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://your-app-name.onrender.com

# Database (Render PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=${DATABASE_HOST}
DB_PORT=${DATABASE_PORT}
DB_DATABASE=${DATABASE_NAME}
DB_USERNAME=${DATABASE_USER}
DB_PASSWORD=${DATABASE_PASSWORD}

# Redis (Render Redis or disable)
CACHE_STORE=file
SESSION_DRIVER=database
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=log

# Stripe (LIVE KEYS for production or TEST for demo)
STRIPE_KEY=${STRIPE_KEY}
STRIPE_SECRET=${STRIPE_SECRET}
STRIPE_WEBHOOK_SECRET=${STRIPE_WEBHOOK_SECRET}

# Xendit
XENDIT_API_KEY=${XENDIT_API_KEY}
XENDIT_WEBHOOK_TOKEN=${XENDIT_WEBHOOK_TOKEN}

# AWS S3 (optional)
AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY_ID}
AWS_SECRET_ACCESS_KEY=${AWS_SECRET_ACCESS_KEY}
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=${AWS_BUCKET}

# Email
MAIL_MAILER=smtp
MAIL_HOST=${MAIL_HOST}
MAIL_PORT=587
MAIL_USERNAME=${MAIL_USERNAME}
MAIL_PASSWORD=${MAIL_PASSWORD}
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,your-frontend.vercel.app
SESSION_DOMAIN=.onrender.com
```

### Step 3: Create Render Build Script

Create `app/render-build.sh`:

```bash
#!/usr/bin/env bash
# exit on error
set -o errexit

# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate app key if not exists
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --show
fi

# Run migrations
php artisan migrate --force

# Seed database (optional - only first time)
# php artisan db:seed --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
```

Make it executable:
```bash
chmod +x app/render-build.sh
```

### Step 4: Create Render Start Script

Create `app/render-start.sh`:

```bash
#!/usr/bin/env bash

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g 'daemon off;'
```

Make it executable:
```bash
chmod +x app/render-start.sh
```

### Step 5: Create Dockerfile for Render

Create `app/Dockerfile.render`:

```dockerfile
FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy nginx config
COPY nginx.render.conf /etc/nginx/sites-available/default

# Expose port
EXPOSE 80

# Start script
CMD ["sh", "render-start.sh"]
```

### Step 6: Create Nginx Config for Render

Create `app/nginx.render.conf`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name _;
    root /var/www/html/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Step 7: Update Laravel CORS for Vercel

Edit `app/config/cors.php`:

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'https://*.vercel.app',
        env('FRONTEND_URL', 'http://localhost:3000'),
    ],
    'allowed_origins_patterns' => ['/\.vercel\.app$/'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

---

## Part 2: Deploy Backend to Render (via GitHub - Super Easy!)

### Step 1: Create PostgreSQL Database

1. Go to https://dashboard.render.com (sign up with GitHub)
2. Click **New** → **PostgreSQL**
3. Configure:
   - **Name**: `billing-db`
   - **Database**: `billing`
   - **Region**: Choose closest to you (e.g., Oregon, Singapore)
   - **Plan**: **Free**
4. Click **Create Database**
5. ✅ Database created! (Leave this page open, we'll link it next)

### Step 2: Deploy Laravel API (Auto-deploy from GitHub!)

1. Click **New** → **Web Service**
2. Click **Connect GitHub** → Select your repository: `laravel-billing-system`
3. Render auto-detects it! Configure:

   **Basic Settings:**
   - **Name**: `billing-api`
   - **Region**: Same as database
   - **Branch**: `main`
   - **Root Directory**: `app`
   - **Environment**: `Docker`
   - **Dockerfile Path**: `Dockerfile.render`
   
   **Instance Type:**
   - **Plan**: **Free**

4. **Auto-Deploy**: ✅ Yes (enabled by default)
   - Every git push will auto-deploy!

5. Click **Advanced** → Add **Environment Variables**:

   ```env
   APP_NAME=Billing System
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=
   APP_URL=https://billing-api.onrender.com
   
   # Database - Click "Add from Database" button!
   # Select your billing-db, it will auto-populate:
   # DATABASE_URL, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
   
   # OR add manually:
   DB_CONNECTION=pgsql
   DB_HOST=<from billing-db dashboard>
   DB_PORT=5432
   DB_DATABASE=billing
   DB_USERNAME=<from billing-db dashboard>
   DB_PASSWORD=<from billing-db dashboard>
   
   # Stripe (use TEST mode for demo)
   STRIPE_KEY=sk_test_51...
   STRIPE_SECRET=sk_test_51...
   STRIPE_WEBHOOK_SECRET=whsec_...
   
   # Cache (use file for free tier)
   CACHE_STORE=file
   SESSION_DRIVER=file
   QUEUE_CONNECTION=database
   
   # Frontend (we'll update this after deploying to Vercel)
   FRONTEND_URL=https://your-app.vercel.app
   SANCTUM_STATEFUL_DOMAINS=your-app.vercel.app,localhost
   SESSION_DOMAIN=.onrender.com
   ```

6. Click **Create Web Service**

### Step 3: Wait for Deployment (5-10 minutes)

- Render automatically:
  - Pulls code from GitHub ✅
  - Builds Docker image ✅
  - Runs migrations ✅
  - Starts your app ✅

- Watch the **Logs** tab to see progress
- When done, you get: `https://billing-api.onrender.com` 🎉

### Step 4: Seed Database (One-time setup)

1. In Render dashboard → Your service → **Shell** tab
2. Click **Launch Shell**
3. Run:
   ```bash
   php artisan db:seed --force
   ```
4. ✅ Test users and plans created!

### Step 5: Test Your API

```bash
curl https://billing-api.onrender.com/api/plans
```

Should return JSON with 3 plans! ✅

---

## Part 3: Deploy Frontend to Vercel (via GitHub - Even Easier!)

### Step 1: Deploy to Vercel (Literally 2 Clicks!)

1. Go to https://vercel.com/new (sign up with GitHub)
2. Click **Import Git Repository**
3. Select your GitHub repository: `laravel-billing-system`
4. Vercel auto-detects React! Configure:

   **Project Settings:**
   - **Framework Preset**: Create React App (auto-detected ✅)
   - **Root Directory**: `frontend` ← **IMPORTANT: Click Edit and set this!**
   - **Build Command**: `npm run build` (auto-filled)
   - **Output Directory**: `build` (auto-filled)
   
5. **Environment Variables** → Add:
   ```
   REACT_APP_API_URL=https://billing-api.onrender.com/api
   ```
   
   *(Replace `billing-api` with your actual Render service name)*

6. Click **Deploy**

### Step 2: Wait for Build (2-3 minutes)

- Vercel automatically:
  - Pulls code from GitHub ✅
  - Installs npm packages ✅
  - Builds React app ✅
  - Deploys to global CDN ✅

### Step 3: Get Your Live URL! 🎉

- Vercel gives you: `https://laravel-billing-system.vercel.app`
- Or custom: `https://your-chosen-name.vercel.app`
- **Auto-SSL included!** (HTTPS) 🔒

### Step 4: Update Render with Frontend URL

Now that you have your Vercel URL, go back to Render:

1. Render dashboard → `billing-api` → **Environment** tab
2. Update these variables:
   ```env
   FRONTEND_URL=https://your-app.vercel.app
   SANCTUM_STATEFUL_DOMAINS=your-app.vercel.app,localhost
   ```
3. Click **Save Changes**
4. Render will auto-redeploy with new settings ✅

### Step 5: Test Your Live App!

1. Go to `https://your-app.vercel.app`
2. Login with:
   - **Customer**: `user@test.com` / `password`
   - **Admin**: `admin@test.com` / `password`
3. ✅ Browse plans, create subscriptions, download invoices!

---

## 🎉 That's It! Your App is LIVE!

- **Frontend**: https://your-app.vercel.app
- **Backend**: https://billing-api.onrender.com
- **Auto-deploys**: Every git push updates both automatically!

---

## Part 4: Configure Stripe Webhooks

### Step 1: Set Up Webhook Endpoint

1. Go to https://dashboard.stripe.com/webhooks
2. Click **Add endpoint**
3. Endpoint URL: `https://billing-api.onrender.com/api/webhook/stripe`
4. Select events:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`
5. Click **Add endpoint**
6. Copy the **Signing secret** (starts with `whsec_`)

### Step 2: Update Render Environment

Add to Render environment variables:
```
STRIPE_WEBHOOK_SECRET=whsec_your_signing_secret
```

---

## Part 5: Testing Your Live Demo

### Test Backend API

```bash
# Get plans
curl https://billing-api.onrender.com/api/plans

# Login
curl -X POST https://billing-api.onrender.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@test.com","password":"password"}'
```

### Test Frontend

1. Go to `https://your-app.vercel.app`
2. Login with: `user@test.com` / `password`
3. Browse plans, create subscription (use Stripe test card: `4242 4242 4242 4242`)

### Stripe Test Cards

For demo purposes, use these test cards:
- **Success**: `4242 4242 4242 4242`
- **Decline**: `4000 0000 0000 0002`
- **3D Secure**: `4000 0025 0000 3155`
- **Expiry**: Any future date
- **CVV**: Any 3 digits
- **ZIP**: Any 5 digits

---

## Part 6: Custom Domain (Optional)

### For Vercel (Frontend)

1. Buy domain (Namecheap, GoDaddy, etc.)
2. In Vercel project settings → **Domains**
3. Add your domain: `billing.yourdomain.com`
4. Update DNS records as instructed by Vercel

### For Render (Backend)

1. In Render web service → **Settings** → **Custom Domain**
2. Add: `api.yourdomain.com`
3. Update DNS with provided CNAME

---

## Troubleshooting

### Issue: CORS Errors

**Fix**: Update Render environment:
```
FRONTEND_URL=https://your-app.vercel.app
SANCTUM_STATEFUL_DOMAINS=your-app.vercel.app
```

Then update `app/config/cors.php` allowed_origins.

### Issue: 500 Errors on Render

**Check logs**:
1. Render dashboard → your service → **Logs**
2. Look for errors

**Common fixes**:
- Run migrations: `php artisan migrate --force`
- Clear cache: `php artisan config:clear`
- Check database connection

### Issue: Vercel Build Fails

**Fix**: Ensure `frontend/package.json` has:
```json
{
  "scripts": {
    "build": "react-scripts build"
  },
  "dependencies": {
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "react-router-dom": "^6.20.0",
    "axios": "^1.6.0"
  }
}
```

### Issue: Database Connection Failed

**Fix**: 
1. Check Render PostgreSQL is running
2. Verify DATABASE_URL in environment
3. Test connection in Shell:
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   ```

---

## Monitoring Your Live Demo

### Render Monitoring

- **Logs**: Render dashboard → your service → Logs
- **Metrics**: Shows CPU, memory usage
- **Health checks**: Automatic

### Vercel Analytics

- **Performance**: Vercel dashboard → Analytics
- **Error tracking**: Runtime logs

---

## Cost Breakdown (Free Tier Limits)

### Render Free Tier
- ✅ 750 hours/month web service (1 service = full month)
- ✅ PostgreSQL database (expires after 90 days - backup data)
- ⚠️ Services spin down after 15 min inactivity (cold start ~30 sec)

### Vercel Free Tier
- ✅ Unlimited deployments
- ✅ 100 GB bandwidth/month
- ✅ Automatic SSL
- ✅ Global CDN

### Stripe
- ✅ Test mode: Free unlimited
- 💰 Live mode: 2.9% + 30¢ per transaction

---

## Going Live with Real Payments

### When Ready for Real Customers

1. **Switch Stripe to Live Mode**:
   - Get live API keys from Stripe dashboard
   - Update Render environment variables
   - Re-configure webhooks with live endpoint

2. **Add Legal Pages**:
   - Terms of Service
   - Privacy Policy
   - Refund Policy

3. **Set Up Customer Support**:
   - Support email
   - Help documentation
   - FAQ page

4. **Marketing**:
   - Landing page
   - SEO optimization
   - Social media presence

---

## Success Checklist

After deployment, verify:

- [ ] Backend API responds at `https://your-api.onrender.com/api/plans`
- [ ] Frontend loads at `https://your-app.vercel.app`
- [ ] Can login with test credentials
- [ ] Can view pricing plans
- [ ] Can create subscription (test mode)
- [ ] Webhooks receive Stripe events
- [ ] Invoices generate and download
- [ ] Admin dashboard shows metrics
- [ ] No CORS errors in browser console
- [ ] SSL certificate active (🔒 in address bar)

---

## Your Live Demo URLs

After deployment, update your portfolio/resume with:

**Live Demo**: https://your-app.vercel.app  
**API Endpoint**: https://billing-api.onrender.com  
**Source Code**: https://github.com/YOUR_USERNAME/laravel-billing-system

**Test Credentials**:
- Customer: user@test.com / password
- Admin: admin@test.com / password

---

## Next Steps

1. ✅ Deploy following this guide
2. ✅ Test all features on live demo
3. ✅ Add demo link to your resume/portfolio
4. ✅ Share with potential employers
5. 🎯 Get hired!

---

**Congratulations!** Your Laravel Billing System will now be live and accessible worldwide as a working demo for your portfolio! 🚀
