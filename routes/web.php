<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\WebAuthController;
use App\Http\Controllers\Dashboard\DashboardController;

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])->name('register.submit');
});

// Dashboard Routes
Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('dashboard.logout');

    // Placeholder routes
    Route::get('/transactions', fn() => view('dashboard.index'))->name('dashboard.transactions.index');
    Route::get('/customers', fn() => view('dashboard.index'))->name('dashboard.customers.index');
    Route::get('/terminals', fn() => view('dashboard.index'))->name('dashboard.terminals.index');
    Route::get('/payouts', fn() => view('dashboard.index'))->name('dashboard.payouts.index');
    Route::get('/api-keys', fn() => view('dashboard.index'))->name('dashboard.api-keys.index');
    Route::get('/webhooks', fn() => view('dashboard.index'))->name('dashboard.webhooks.index');
    Route::get('/settings', fn() => view('dashboard.index'))->name('dashboard.settings');
});

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});
