<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('merchant_id');
            $table->uuid('customer_id')->nullable();
            $table->string('reference')->unique();
            $table->string('type')->default('payment');
            $table->string('status')->default('pending');
            $table->bigInteger('amount');
            $table->bigInteger('amount_refunded')->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('payment_method_type')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_last_four')->nullable();
            $table->string('card_exp_month')->nullable();
            $table->string('card_exp_year')->nullable();
            $table->string('source')->default('api');
            $table->uuid('terminal_id')->nullable();
            $table->string('processor_type')->nullable();
            $table->string('processor_transaction_id')->nullable();
            $table->json('processor_response')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->string('description')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('failure_code')->nullable();
            $table->string('receipt_email')->nullable();
            $table->string('receipt_url')->nullable();
            $table->json('billing_address')->nullable();
            $table->json('shipping_address')->nullable();
            $table->boolean('is_test')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('disputed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('merchant_id')->references('id')->on('merchants')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->unique(['merchant_id', 'idempotency_key']);
            $table->index(['merchant_id', 'status']);
            $table->index(['merchant_id', 'type']);
            $table->index(['merchant_id', 'created_at']);
            $table->index('processor_transaction_id');
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
