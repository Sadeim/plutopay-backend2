<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('merchant_id');
            $table->string('reference')->unique();
            $table->bigInteger('amount');
            $table->bigInteger('fee')->default(0);
            $table->bigInteger('net_amount');
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending');
            $table->string('destination_type')->nullable();
            $table->string('destination_last_four')->nullable();
            $table->string('processor_payout_id')->nullable();
            $table->json('processor_response')->nullable();
            $table->text('failure_reason')->nullable();
            $table->boolean('is_test')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('estimated_arrival_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            $table->foreign('merchant_id')->references('id')->on('merchants')->onDelete('cascade');
            $table->index(['merchant_id', 'status']);
            $table->index(['merchant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
