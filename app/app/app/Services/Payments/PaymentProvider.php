<?php

namespace App\Services\Payments;

use App\Models\Plan;
use App\Models\User;

interface PaymentProvider
{
    public function createCheckoutSession(User $user, Plan $plan): array;

    public function handleWebhook(array $payload, ?string $signature = null): void;
}
