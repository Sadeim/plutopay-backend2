<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MerchantController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\RefundController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\TerminalController;
use App\Http\Controllers\Api\V1\WebhookEndpointController;
use App\Http\Controllers\Api\V1\WebhookTestController;
use App\Http\Controllers\Api\V1\PayoutController;
use App\Http\Controllers\Api\V1\DisputeController;
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

// Dashboard Auth
Route::prefix('dashboard')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Dashboard Protected
Route::prefix('dashboard')->middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// ==========================================
// API v1 (API Key Auth)
// ==========================================

Route::prefix('v1')->middleware('api.auth')->group(function () {
    Route::get('/merchant', [MerchantController::class, 'show']);

    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);

    Route::post('/transactions/{transaction}/refunds', [RefundController::class, 'store']);

    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('terminals', TerminalController::class);
    Route::apiResource('webhook-endpoints', WebhookEndpointController::class);
    Route::post('/webhook-endpoints/{endpoint}/test', [WebhookTestController::class, 'test']);

    Route::get('/payouts', [PayoutController::class, 'index']);
    Route::get('/payouts/{id}', [PayoutController::class, 'show']);

    Route::get('/disputes', [DisputeController::class, 'index']);
    Route::get('/disputes/{id}', [DisputeController::class, 'show']);
});
