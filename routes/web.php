<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\WebAuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\TransactionController;
use App\Http\Controllers\Dashboard\TransactionDetailController;
use App\Http\Controllers\Dashboard\CustomerController;
use App\Http\Controllers\Dashboard\ApiKeyController;
use App\Http\Controllers\Dashboard\WebhookController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\TerminalController;
use App\Http\Controllers\Dashboard\PayoutController;
use App\Http\Controllers\Dashboard\PosController;
use App\Http\Controllers\Pos\StandalonePosController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) return redirect('/dashboard');
    return view('landing');
});

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);
});



// Auto-redirect /pos to merchant POS if logged in
Route::get('/pos', function () {
    if (auth()->check()) {
        return redirect('/pos/' . auth()->user()->merchant_id . '/terminal');
    }
    return view('pos.standalone.select-merchant');
})->name('pos.redirect');

Route::post("/pos", function (Illuminate\Http\Request $request) {
    $request->validate(["email" => "required|email", "password" => "required"]);
    if (auth()->attempt($request->only("email", "password"))) {
        $request->session()->regenerate();
        $user = auth()->user();
        return redirect("/pos/" . $user->merchant_id . "/terminal");
    }
    return back()->with("error", "Invalid credentials.")->withInput($request->only("email"));
})->name("pos.login");

// Standalone POS (external link for merchants)
Route::prefix('pos/{merchantId}')->name('standalone.pos.')->group(function () {
    Route::get('/', [StandalonePosController::class, 'login'])->name('login');
    Route::post('/authenticate', [StandalonePosController::class, 'authenticate'])->name('authenticate');
    Route::middleware('auth')->group(function () {
        Route::get('/terminal', [StandalonePosController::class, 'terminal'])->name('terminal');
        Route::post('/charge', [StandalonePosController::class, 'charge'])->name('charge');
        Route::get('/status/{txnId}', [StandalonePosController::class, 'status'])->name('status');
        Route::post('/cancel/{txnId}', [StandalonePosController::class, 'cancel'])->name('cancel');
        Route::post('/logout', [StandalonePosController::class, 'logout'])->name('logout');
    });
});

// Dashboard
Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{id}', [TransactionDetailController::class, 'show'])->name('transactions.show');
    Route::post('/transactions/{id}/refund', [TransactionDetailController::class, 'refund'])->name('transactions.refund');

    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');

    // Terminals
    Route::get('/terminals', [TerminalController::class, 'index'])->name('terminals.index');
    Route::post('/terminals', [TerminalController::class, 'store'])->name('terminals.store');
    Route::post('/terminals/fetch-readers', [TerminalController::class, 'fetchReaders'])->name('terminals.fetch-readers');
    Route::post('/terminals/import', [TerminalController::class, 'importReaders'])->name('terminals.import');
    Route::put('/terminals/{id}', [TerminalController::class, 'update'])->name('terminals.update');
    Route::delete('/terminals/{id}', [TerminalController::class, 'destroy'])->name('terminals.destroy');

    // Payouts
    Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');

    // POS
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/charge', [PosController::class, 'charge'])->name('pos.charge');
    Route::get('/pos/status/{id}', [PosController::class, 'status'])->name('pos.status');
    Route::post('/pos/cancel/{id}', [PosController::class, 'cancel'])->name('pos.cancel');

    // API Keys
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::patch('/api-keys/{id}/revoke', [ApiKeyController::class, 'revoke'])->name('api-keys.revoke');

    // Webhooks
    Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
    Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::put('/webhooks/{id}', [WebhookController::class, 'update'])->name('webhooks.update');
    Route::patch('/webhooks/{id}/toggle', [WebhookController::class, 'toggle'])->name('webhooks.toggle');
    Route::delete('/webhooks/{id}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    Route::post('/webhooks/{id}/test', [WebhookController::class, 'test'])->name('webhooks.test');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/business', [SettingsController::class, 'updateBusiness'])->name('settings.business');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
});
