# Dockerized Laravel Payment & Billing (Stripe/Xendit)

This project is designed so you **do NOT need PHP / MySQL / Composer locally**.
Everything runs inside Docker.

## Structure

- `docker-compose.yml` – services (php-fpm, nginx, mysql, composer)
- `docker/php/Dockerfile` – PHP 8.3 + extensions
- `docker/nginx/default.conf` – Nginx vhost for Laravel
- `module/` – all Laravel billing code (models, services, controllers, routes, migrations, config, views)

You will:
1. Generate a clean Laravel app *inside Docker*.
2. Copy the `module/` contents into that app.
3. Run migrations & install Stripe SDK.

---

## 1. Start from this folder

```bash
cd billing_docker_full   # (or whatever name you extracted to)
```

## 2. Create Laravel app inside Docker (no local PHP)

This will create `./app` using Composer in a container:

```bash
docker compose run --rm composer create-project laravel/laravel app
```

> After this, your Laravel app lives in the `app/` directory.

## 3. Copy the billing module into the Laravel app

On Linux/macOS:

```bash
cp -r module/* app/
```

On Windows, you can drag & drop `module` contents into `app` and merge.

## 4. Install Stripe PHP SDK (inside the app)

```bash
docker compose run --rm composer require stripe/stripe-php
```

## 5. Configure environment

Copy any billing env keys from `module/.env.example` into `app/.env` and set your:

- Stripe keys
- Xendit keys (optional)
- DB settings (must match `docker-compose.yml`)

Example database section in `app/.env`:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=billing
DB_USERNAME=app
DB_PASSWORD=secret
```

## 6. Bring up the stack

```bash
docker compose up -d
```

- App URL: http://localhost:8000

## 7. Run migrations inside the PHP container

```bash
docker compose exec app php artisan migrate
```

> At this point, you have:
> - Users table extended with billing fields + role
> - Plans, Subscriptions, Invoices tables
> - API routes for checkout, subscriptions, invoices
> - Webhook endpoints for Stripe and Xendit
> - Basic admin dashboard (`/admin`, protect via `role = 'admin'`)

## 8. Mark an admin user

After registering a user through your usual flow, set their role:

```sql
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

Now `/admin` should be accessible for that user (the `EnsureAdmin` middleware is used).

---

## Notes

- All billing code is under `module/` and is meant to **augment** a stock Laravel app.
- You can mount this into any environment that can run Docker (Linux/WSL/Windows/macOS).
- For a portfolio, focus on:
  - Stripe session creation endpoint (`/api/checkout/{plan:slug}`)
  - Webhooks (`/webhook/stripe`, `/webhook/xendit`)
  - Admin plans/invoices UI
