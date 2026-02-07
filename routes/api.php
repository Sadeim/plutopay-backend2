<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MerchantController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\RefundController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\TerminalController;
use App\Http\Controllers\Api\V1\TerminalPaymentController;
use App\Http\Controllers\Api\V1\WebhookEndpointController;
use App\Http\Controllers\Api\V1\WebhookTestController;
use App\Http\Controllers\Api\V1\PayoutController;
use App\Http\Controllers\Api\V1\DisputeController;
use App\Http\Controllers\Api\V1\StripeWebhookController;
use App\Http\Controllers\Dashboard\AuthController;

// ==========================================
// Public Routes
// ==========================================

Route::get('/v1/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'PlutoPay API',
        'version' => 'v1',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// ==========================================
// Stripe Webhooks (no auth - signature verified internally)
// ==========================================

Route::post('/v1/stripe/webhook', [StripeWebhookController::class, 'handle']);
Route::post('/v1/stripe/webhook/connect', [StripeWebhookController::class, 'handleConnect']);

// ==========================================
// Dashboard Auth
// ==========================================

Route::prefix('dashboard')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::prefix('dashboard')->middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// ==========================================
// API v1 (API Key Auth)
// ==========================================

Route::prefix('v1')->middleware('api.auth')->group(function () {
    // Merchant
    Route::get('/merchant', [MerchantController::class, 'show']);

    // Transactions (Payments)
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
    Route::post('/transactions/{id}/capture', [TransactionController::class, 'capture']);
    Route::post('/transactions/{id}/cancel', [TransactionController::class, 'cancel']);

    // Refunds
    Route::post('/transactions/{transaction}/refunds', [RefundController::class, 'store']);

    // Terminal Payment Flow
    Route::post('/terminal/connection-token', [TerminalPaymentController::class, 'connectionToken']);
    Route::post('/terminal/create-payment', [TerminalPaymentController::class, 'createPayment']);
    Route::post('/terminal/process-payment', [TerminalPaymentController::class, 'processPayment']);
    Route::post('/terminal/simulate-payment', [TerminalPaymentController::class, 'simulatePayment']);
    Route::get('/terminal/readers', [TerminalPaymentController::class, 'readers']);

    // Customers
    Route::apiResource('customers', CustomerController::class);

    // Terminals (management)
    Route::apiResource('terminals', TerminalController::class);

    // Webhook Endpoints
    Route::apiResource('webhook-endpoints', WebhookEndpointController::class);
    Route::post('/webhook-endpoints/{endpoint}/test', [WebhookTestController::class, 'test']);

    // Payouts
    Route::get('/payouts', [PayoutController::class, 'index']);
    Route::get('/payouts/{id}', [PayoutController::class, 'show']);

    // Disputes
    Route::get('/disputes', [DisputeController::class, 'index']);
    Route::get('/disputes/{id}', [DisputeController::class, 'show']);
});
