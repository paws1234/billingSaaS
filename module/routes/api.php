<?php

use App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\API\InvoiceController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [ProfileController::class, 'me']);

    Route::get('/plans', [SubscriptionController::class, 'plans']);
    Route::post('/checkout/{plan:slug}', [CheckoutController::class, 'createSession']);

    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/subscriptions/cancel', [SubscriptionController::class, 'cancel']);

    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
});
