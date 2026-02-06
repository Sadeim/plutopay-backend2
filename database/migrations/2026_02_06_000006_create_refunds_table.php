<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('merchant_id');
            $table->uuid('transaction_id');
            $table->string('reference')->unique();
            $table->bigInteger('amount');
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('processor_refund_id')->nullable();
            $table->json('processor_response')->nullable();
            $table->boolean('is_test')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->foreign('merchant_id')->references('id')->on('merchants')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->index(['merchant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
