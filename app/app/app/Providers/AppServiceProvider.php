<?php

namespace App\Providers;

use App\Services\Payments\PaymentProvider;
use App\Services\Payments\StripePaymentService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind PaymentProvider interface to StripePaymentService
        $this->app->bind(PaymentProvider::class, StripePaymentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
