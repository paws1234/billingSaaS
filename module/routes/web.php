<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Webhook\StripeWebhookController;
use App\Http\Controllers\Webhook\XenditWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle']);
Route::post('/webhook/xendit', [XenditWebhookController::class, 'handle']);

Route::middleware(['auth', 'admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});
