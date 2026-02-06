<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('webhook_event_id');
            $table->uuid('webhook_endpoint_id');
            $table->string('url');
            $table->integer('http_status')->nullable();
            $table->text('request_headers')->nullable();
            $table->text('request_body')->nullable();
            $table->text('response_headers')->nullable();
            $table->text('response_body')->nullable();
            $table->integer('attempt_number')->default(1);
            $table->integer('response_time_ms')->nullable();
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->foreign('webhook_event_id')->references('id')->on('webhook_events')->onDelete('cascade');
            $table->foreign('webhook_endpoint_id')->references('id')->on('webhook_endpoints')->onDelete('cascade');
            $table->index('status');
            $table->index('next_retry_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
