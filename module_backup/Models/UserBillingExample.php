<?php

namespace App\Models;

trait UserBillingFields
{
    // In User model, add:
    // use UserBillingFields;

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
