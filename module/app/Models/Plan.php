<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'provider',
        'provider_plan_id',
        'interval',
        'amount',
        'currency',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
