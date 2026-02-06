<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terminals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('merchant_id');
            $table->string('name');
            $table->string('serial_number')->nullable();
            $table->string('model')->nullable();
            $table->string('status')->default('offline');
            $table->string('location_name')->nullable();
            $table->string('location_address')->nullable();
            $table->string('processor_terminal_id')->nullable();
            $table->string('processor_location_id')->nullable();
            $table->json('processor_metadata')->nullable();
            $table->string('firmware_version')->nullable();
            $table->integer('battery_level')->nullable();
            $table->boolean('is_test')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('paired_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('merchant_id')->references('id')->on('merchants')->onDelete('cascade');
            $table->index(['merchant_id', 'status']);
            $table->unique(['merchant_id', 'serial_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terminals');
    }
};
