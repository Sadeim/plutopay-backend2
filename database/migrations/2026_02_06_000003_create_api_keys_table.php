<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('merchant_id');
            $table->string('name');
            $table->string('type');
            $table->string('key')->unique();
            $table->string('key_hash')->unique();
            $table->string('key_last_four');
            $table->boolean('is_test')->default(false);
            $table->json('scopes')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->foreign('merchant_id')->references('id')->on('merchants')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('merchant_users')->onDelete('set null');
            $table->index(['merchant_id', 'is_test']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
