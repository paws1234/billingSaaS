<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider');
            $table->string('provider_invoice_id')->nullable();
            $table->string('provider_payment_intent_id')->nullable();
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('CAD');
            $table->enum('status', ['draft', 'open', 'paid', 'void'])->default('open');
            $table->string('receipt_path')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
