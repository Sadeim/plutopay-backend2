<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MerchantController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\RefundController;

// Health check (public)
Route::get('/v1/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'PlutoPay API',
        'version' => 'v1',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// API v1 (authenticated)
Route::prefix('v1')->middleware('api.auth')->group(function () {
    // Merchant
    Route::get('/merchant', [MerchantController::class, 'show']);

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);

    // Refunds
    Route::post('/transactions/{transaction}/refunds', [RefundController::class, 'store']);
});
