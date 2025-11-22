# Laravel Billing System - Quick Start Script
# Run this in PowerShell from the project root directory

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "  Laravel Billing System - Setup & Start" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""

# Check if Docker is running
Write-Host "Checking Docker..." -ForegroundColor Yellow
$dockerRunning = docker ps 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Docker is not running. Please start Docker Desktop and try again." -ForegroundColor Red
    exit 1
}
Write-Host "✓ Docker is running" -ForegroundColor Green
Write-Host ""

# Install Composer dependencies
Write-Host "Installing PHP dependencies..." -ForegroundColor Yellow
docker-compose run --rm composer install
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Composer dependencies installed" -ForegroundColor Green
} else {
    Write-Host "! Warning: Composer install had issues (this may be normal)" -ForegroundColor Yellow
}
Write-Host ""

# Install additional packages
Write-Host "Installing additional PHP packages (PDF, AWS, Stripe)..." -ForegroundColor Yellow
Write-Host "This may take a few minutes..." -ForegroundColor Gray
docker-compose run --rm composer require barryvdh/laravel-dompdf aws/aws-sdk-php stripe/stripe-php
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Additional packages installed" -ForegroundColor Green
} else {
    Write-Host "! Warning: Some packages may need manual installation" -ForegroundColor Yellow
    Write-Host "  Run manually: docker-compose run --rm composer require <package>" -ForegroundColor Gray
}
Write-Host ""

# Check .env file
Write-Host "Checking .env configuration..." -ForegroundColor Yellow
if (Test-Path "app\app\.env") {
    Write-Host "✓ .env file exists" -ForegroundColor Green
} else {
    if (Test-Path "app\app\.env.example") {
        Write-Host "Creating .env from .env.example..." -ForegroundColor Yellow
        Copy-Item "app\app\.env.example" "app\app\.env"
        Write-Host "✓ .env file created" -ForegroundColor Green
        Write-Host "! IMPORTANT: Edit app\app\.env and add your Stripe/AWS keys" -ForegroundColor Yellow
    } else {
        Write-Host "! Warning: No .env.example found" -ForegroundColor Yellow
    }
}
Write-Host ""

# Generate app key
Write-Host "Generating application key..." -ForegroundColor Yellow
docker-compose run --rm app php artisan key:generate
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Application key generated" -ForegroundColor Green
} else {
    Write-Host "! Warning: Key generation failed" -ForegroundColor Yellow
}
Write-Host ""

# Run migrations
Write-Host "Running database migrations..." -ForegroundColor Yellow
docker-compose run --rm app php artisan migrate --force
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Database migrated" -ForegroundColor Green
} else {
    Write-Host "! Warning: Migrations may have failed (run manually if needed)" -ForegroundColor Yellow
}
Write-Host ""

# Seed database
Write-Host "Seeding database with test data..." -ForegroundColor Yellow
docker-compose run --rm app php artisan db:seed --force
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Database seeded" -ForegroundColor Green
} else {
    Write-Host "! Warning: Seeding may have failed" -ForegroundColor Yellow
}
Write-Host ""

# Set permissions
Write-Host "Setting permissions..." -ForegroundColor Yellow
docker-compose run --rm app chmod -R 777 storage bootstrap/cache
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Permissions set" -ForegroundColor Green
}
Write-Host ""

# Start services
Write-Host "Starting all Docker containers..." -ForegroundColor Yellow
docker-compose up -d
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ All containers started" -ForegroundColor Green
} else {
    Write-Host "! Error: Failed to start containers" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Wait for services
Write-Host "Waiting for services to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 5
Write-Host "✓ Services should be ready" -ForegroundColor Green
Write-Host ""

# Success message
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "  ✓ SETUP COMPLETE!" -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Your Laravel Billing System is now running:" -ForegroundColor White
Write-Host ""
Write-Host "  Frontend:  http://localhost:3000" -ForegroundColor Cyan
Write-Host "  Backend:   http://localhost:8000" -ForegroundColor Cyan
Write-Host "  API Docs:  http://localhost:8000/api/plans" -ForegroundColor Cyan
Write-Host ""
Write-Host "Test Accounts:" -ForegroundColor White
Write-Host "  Customer:  user@test.com / password" -ForegroundColor Yellow
Write-Host "  Admin:     admin@test.com / password" -ForegroundColor Yellow
Write-Host ""
Write-Host "View logs:" -ForegroundColor White
Write-Host "  docker-compose logs -f" -ForegroundColor Gray
Write-Host ""
Write-Host "Stop services:" -ForegroundColor White
Write-Host "  docker-compose down" -ForegroundColor Gray
Write-Host ""
Write-Host "Full documentation:" -ForegroundColor White
Write-Host "  See IMPLEMENTATION_GUIDE.md" -ForegroundColor Gray
Write-Host ""
Write-Host "==================================================" -ForegroundColor Cyan
