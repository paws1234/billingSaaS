<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\User;
use App\Services\Payments\PaymentProvider;

class BillingService
{
    public function __construct(protected PaymentProvider $provider)
    {
    }

    public function startCheckout(User $user, Plan $plan): array
    {
        return $this->provider->createCheckoutSession($user, $plan);
    }
}
