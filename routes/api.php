<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MerchantController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\RefundController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\TerminalController;
use App\Http\Controllers\Api\V1\WebhookEndpointController;
use App\Http\Controllers\Api\V1\PayoutController;
use App\Http\Controllers\Api\V1\DisputeController;

// Health check
Route::get('/v1/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'PlutoPay API',
        'version' => 'v1',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// API v1
Route::prefix('v1')->middleware('api.auth')->group(function () {

    // Merchant
    Route::get('/merchant', [MerchantController::class, 'show']);

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);

    // Refunds
    Route::post('/transactions/{transaction}/refunds', [RefundController::class, 'store']);

    // Customers
    Route::apiResource('customers', CustomerController::class);

    // Terminals
    Route::apiResource('terminals', TerminalController::class);

    // Webhook Endpoints
    Route::apiResource('webhook-endpoints', WebhookEndpointController::class);

    // Payouts
    Route::get('/payouts', [PayoutController::class, 'index']);
    Route::get('/payouts/{id}', [PayoutController::class, 'show']);

    // Disputes
    Route::get('/disputes', [DisputeController::class, 'index']);
    Route::get('/disputes/{id}', [DisputeController::class, 'show']);
});
