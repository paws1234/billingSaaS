<?php

return [
    'provider' => env('BILLING_PROVIDER', 'stripe'),
    'default_currency' => env('BILLING_DEFAULT_CURRENCY', 'CAD'),
    'tax_percent' => (float) env('BILLING_TAX_PERCENT', 0),
    'trial_days' => (int) env('BILLING_TRIAL_DAYS', 14),
];
