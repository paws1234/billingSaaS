# Laravel Billing System - Complete Implementation Guide

## Overview
Production-ready Laravel 12 billing system with Stripe/Xendit payments, subscription management, invoicing, PDF receipts, AWS S3 storage, and React frontend.

## Features Implemented

### Backend (Laravel API)
- ✅ **User Authentication** - Laravel Sanctum token-based API auth
- ✅ **Subscription Management** - Create, upgrade, downgrade, cancel subscriptions
- ✅ **Payment Processing** - Stripe & Xendit integrations
- ✅ **Trial Periods** - 14-day free trial for new subscriptions
- ✅ **Invoicing** - Automatic invoice generation on payments
- ✅ **PDF Receipts** - Generate professional PDF invoices
- ✅ **AWS S3 Storage** - Upload receipts to S3 for cloud storage
- ✅ **Redis Caching** - Redis for cache, sessions, and queues
- ✅ **Admin Dashboard** - Admin-only routes for metrics and management
- ✅ **Webhook Handling** - Process Stripe/Xendit webhooks

### Frontend (React)
- ✅ **Customer Portal** - Dashboard, plans, subscriptions, invoices
- ✅ **Admin Dashboard** - Revenue metrics, subscription stats
- ✅ **Responsive UI** - Mobile-friendly design
- ✅ **Secure Auth** - Token-based authentication with auto-logout
- ✅ **Dockerized** - Complete Docker setup, no local npm install needed

### DevOps
- ✅ **Docker Compose** - Multi-container orchestration
- ✅ **PHP 8.3** - Modern PHP with all extensions
- ✅ **MySQL 8** - Relational database
- ✅ **Redis** - In-memory caching
- ✅ **Nginx** - Web server
- ✅ **React** - Node 18 frontend container

## System Requirements
- Docker Desktop (Windows/Mac/Linux)
- No local PHP, Composer, Node, or npm required - everything runs in Docker

## Installation

### Step 1: Clone/Extract Project
```bash
cd c:\Users\Paws\Downloads\billing_docker_full
```

### Step 2: Install PHP Dependencies
```bash
docker-compose run --rm composer install
docker-compose run --rm composer require barryvdh/laravel-dompdf aws/aws-sdk-php stripe/stripe-php
```

### Step 3: Configure Environment
Copy `.env.example` to `.env` and update:
```bash
cp app/app/.env.example app/app/.env
```

Edit `app/app/.env`:
```env
# App
APP_NAME="Billing System"
APP_ENV=local
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=billing
DB_USERNAME=app
DB_PASSWORD=secret

# Redis
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379

# Stripe
STRIPE_KEY=sk_test_YOUR_KEY
STRIPE_SECRET=sk_test_YOUR_SECRET
STRIPE_WEBHOOK_SECRET=whsec_YOUR_WEBHOOK_SECRET

# Xendit
XENDIT_API_KEY=xnd_YOUR_KEY
XENDIT_WEBHOOK_TOKEN=YOUR_WEBHOOK_TOKEN

# AWS S3 (optional - for receipt storage)
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=billing-receipts

# Email (for receipts)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

### Step 4: Generate Application Key
```bash
docker-compose run --rm app php artisan key:generate
```

### Step 5: Run Migrations and Seed Data
```bash
docker-compose run --rm app php artisan migrate
docker-compose run --rm app php artisan db:seed
```

This creates:
- **Admin User**: admin@test.com / password (role: admin)
- **Customer User**: user@test.com / password (role: customer)
- **Plans**: Basic ($10/month), Pro ($20/month), Enterprise ($50/month)

### Step 6: Start All Services
```bash
docker-compose up -d
```

This starts:
- **Backend API**: http://localhost:8000
- **Frontend**: http://localhost:3000
- **MySQL**: localhost:3307
- **Redis**: localhost:6379

### Step 7: Set Permissions (if needed)
```bash
docker-compose exec app chmod -R 777 storage bootstrap/cache
docker-compose exec app chmod 666 database/database.sqlite
```

## API Endpoints

### Authentication
- `POST /api/login` - Login (returns token)
- `POST /api/logout` - Logout
- `GET /api/me` - Get current user

### Plans
- `GET /api/plans` - List all plans

### Subscriptions
- `GET /api/subscriptions` - List user subscriptions
- `POST /api/checkout` - Create subscription checkout
- `POST /api/subscriptions/{id}/cancel` - Cancel subscription
- `POST /api/subscriptions/{id}/resume` - Resume subscription
- `POST /api/subscriptions/{id}/change-plan` - Upgrade/downgrade

### Invoices
- `GET /api/invoices` - List user invoices
- `GET /api/invoices/{id}/download` - Download PDF receipt

### Admin (requires admin role)
- `GET /api/admin/subscriptions` - All subscriptions
- `GET /api/admin/invoices` - All invoices

### Webhooks
- `POST /api/webhook/stripe` - Stripe webhook handler
- `POST /api/webhook/xendit` - Xendit webhook handler

## Testing API with cURL

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@test.com","password":"password"}'

# Returns: {"token":"1|xyz123..."}

# Get Plans
curl http://localhost:8000/api/plans \
  -H "Authorization: Bearer YOUR_TOKEN"

# Create Subscription Checkout
curl -X POST http://localhost:8000/api/checkout \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"plan_id":1,"provider":"stripe","return_url":"http://localhost:3000/subscriptions"}'

# List Subscriptions
curl http://localhost:8000/api/subscriptions \
  -H "Authorization: Bearer YOUR_TOKEN"

# Change Plan (upgrade/downgrade)
curl -X POST http://localhost:8000/api/subscriptions/1/change-plan \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"plan_id":2}'

# Download Invoice PDF
curl http://localhost:8000/api/invoices/1/download \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output invoice.pdf
```

## Frontend Usage

### Customer Portal
1. Navigate to http://localhost:3000
2. Login with `user@test.com` / `password`
3. **Dashboard** - View account info and quick actions
4. **Plans** - Browse and subscribe to plans
5. **Subscriptions** - Manage active subscriptions, upgrade/downgrade
6. **Invoices** - View billing history, download PDFs

### Admin Dashboard
1. Login with `admin@test.com` / `password`
2. **Admin Dashboard** link appears in navigation
3. View metrics: total revenue, active subscriptions, users, pending invoices
4. See recent subscriptions and invoices

## Project Structure

```
billing_docker_full/
├── app/app/                    # Laravel application
│   ├── app/
│   │   ├── Http/Controllers/API/  # API controllers
│   │   ├── Models/                # Eloquent models
│   │   └── Services/              # Business logic services
│   ├── config/                    # Configuration files
│   ├── database/migrations/       # Database migrations
│   ├── routes/api.php            # API routes
│   └── resources/views/receipts/ # PDF templates
├── frontend/                     # React application
│   ├── src/
│   │   ├── components/          # React components
│   │   └── services/api.js      # Axios API client
│   ├── Dockerfile               # Frontend container
│   └── package.json             # Node dependencies
├── docker/                      # Docker configurations
│   ├── nginx/default.conf       # Nginx config
│   └── php/Dockerfile           # PHP container
└── docker-compose.yml           # Multi-container orchestration
```

## Key Features Explained

### Trial Periods
- New subscriptions automatically get 14-day free trial
- Configured in `config/billing.php`
- Trial end date stored in `subscriptions.trial_ends_at`
- Payment processors handle trial logic

### Subscription Upgrades/Downgrades
- Endpoint: `POST /api/subscriptions/{id}/change-plan`
- **Proration**: Automatic credit/charge calculation
- Updates Stripe subscription immediately
- Preserves subscription metadata

### PDF Receipts
- Generated using DomPDF
- Template: `resources/views/receipts/invoice.blade.php`
- Service: `app/Services/ReceiptService.php`
- Automatic generation on successful payment
- Optional S3 upload for cloud storage

### AWS S3 Integration
- Configure AWS credentials in `.env`
- Receipts uploaded to S3 bucket
- Public/temporary URLs for downloads
- Falls back to local storage if S3 unavailable

### Redis Caching
- Session storage (faster than database)
- Cache layer for frequently accessed data
- Queue backend for background jobs
- Container: `billing-redis`

### Payment Webhooks
- **Stripe**: `POST /api/webhook/stripe`
- **Xendit**: `POST /api/webhook/xendit`
- Verify signatures before processing
- Handle: payment success, subscription canceled, invoice paid

## Troubleshooting

### Issue: Permission Denied on Storage
```bash
docker-compose exec app chmod -R 777 storage bootstrap/cache
```

### Issue: Database Not Found
```bash
docker-compose run --rm app php artisan migrate:fresh --seed
```

### Issue: Frontend Can't Connect to API
- Check `REACT_APP_API_URL` in docker-compose.yml
- Ensure backend is running: `docker-compose ps`
- Check browser console for CORS errors

### Issue: Sanctum Token Not Working
```bash
docker-compose run --rm app php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
docker-compose run --rm app php artisan migrate
```

### Issue: Composer Packages Not Installing
```bash
# Manually enter container
docker-compose run --rm composer bash
composer require barryvdh/laravel-dompdf
composer require aws/aws-sdk-php
composer require stripe/stripe-php
exit
```

### View Container Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f frontend
```

## Development Workflow

### Backend Development
```bash
# Enter PHP container
docker-compose exec app bash

# Run artisan commands
php artisan make:controller MyController
php artisan make:model MyModel -m
php artisan migrate
php artisan tinker

# Run tests
php artisan test
```

### Frontend Development
```bash
# Frontend runs in watch mode, changes auto-reload
# Edit files in frontend/src/
# Browser at http://localhost:3000 hot-reloads
```

### Database Management
```bash
# MySQL CLI
docker-compose exec mysql mysql -u app -psecret billing

# Run specific migration
docker-compose run --rm app php artisan migrate:rollback --step=1
docker-compose run --rm app php artisan migrate
```

## Production Deployment

### Environment Updates
1. Set `APP_ENV=production`
2. Set `APP_DEBUG=false`
3. Use strong `APP_KEY`
4. Configure real payment API keys
5. Set up production database
6. Configure AWS S3 credentials
7. Set up SSL/HTTPS

### Build for Production
```bash
# Backend
docker-compose run --rm composer install --no-dev --optimize-autoloader
docker-compose run --rm app php artisan config:cache
docker-compose run --rm app php artisan route:cache
docker-compose run --rm app php artisan view:cache

# Frontend
cd frontend
npm run build
# Serve build/ folder with nginx
```

## Testing

### Manual API Testing
See "Testing API with cURL" section above

### PHPUnit Tests (To Be Created)
```bash
docker-compose run --rm app php artisan test

# Feature tests for:
# - Authentication endpoints
# - Subscription CRUD
# - Invoice generation
# - PDF creation
# - Webhook processing

# Unit tests for:
# - BillingService
# - StripePaymentService
# - ReceiptService
```

## Next Steps

### Recommended Enhancements
1. ✅ **Tests** - PHPUnit feature and unit tests
2. **Email Notifications** - Subscription confirmations, receipt emails
3. **Usage Tracking** - Metered billing for usage-based plans
4. **Coupons/Discounts** - Promo codes and referral discounts
5. **Multi-currency** - Support international payments
6. **Dunning Management** - Failed payment retry logic
7. **Analytics Dashboard** - Charts with Chart.js/Recharts
8. **Export Data** - CSV export for subscriptions/invoices

## Credits
Developed for Canadian Developer Job Portfolio
Laravel 12 | React 18 | Docker Compose | Stripe | AWS

## License
MIT
