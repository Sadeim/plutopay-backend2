<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_name');
            $table->string('display_name')->nullable();
            $table->string('business_type')->default('individual');
            $table->string('business_category')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country', 2)->default('US');
            $table->string('default_currency', 3)->default('USD');
            $table->string('timezone')->default('UTC');
            $table->string('locale')->default('en');
            $table->string('status')->default('pending');
            $table->string('kyc_status')->default('not_started');
            $table->timestamp('kyc_submitted_at')->nullable();
            $table->timestamp('kyc_approved_at')->nullable();
            $table->text('kyc_rejection_reason')->nullable();
            $table->string('processor_type')->default('stripe');
            $table->string('processor_account_id')->nullable();
            $table->json('processor_metadata')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('icon_url')->nullable();
            $table->string('primary_color')->nullable();
            $table->boolean('test_mode')->default(true);
            $table->string('webhook_secret')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('kyc_status');
            $table->index('processor_type');
            $table->index('test_mode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
