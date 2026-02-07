<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Terminal;
use App\Models\Transaction;
use App\Services\Payment\PaymentProcessorFactory;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StandalonePosController extends Controller
{
    /**
     * Show login page for merchant POS
     */
    public function login(string $merchantId)
    {
        $merchant = Merchant::findOrFail($merchantId);

        if (Auth::check() && Auth::user()->merchant_id === $merchant->id) {
            return redirect()->route('standalone.pos.terminal', $merchant->id);
        }

        return view('pos.standalone.login', compact('merchant'));
    }

    /**
     * Process login
     */
    public function authenticate(Request $request, string $merchantId)
    {
        $merchant = Merchant::findOrFail($merchantId);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            if ($user->merchant_id !== $merchant->id) {
                Auth::logout();
                return back()->with('error', 'You do not have access to this POS.');
            }

            $request->session()->regenerate();
            return redirect()->route('standalone.pos.terminal', $merchant->id);
        }

        return back()->with('error', 'Invalid credentials.');
    }

    /**
     * Show POS terminal page
     */
    public function terminal(string $merchantId)
    {
        $merchant = Merchant::findOrFail($merchantId);
        $user = Auth::user();

        if (!$user || $user->merchant_id !== $merchant->id) {
            return redirect()->route('standalone.pos.login', $merchant->id);
        }

        $terminals = Terminal::where('merchant_id', $merchant->id)
            ->where('status', 'online')
            ->get();

        $currency = $merchant->default_currency ?? 'USD';

        $recentTransactions = Transaction::where('merchant_id', $merchant->id)
            ->where('payment_method_type', 'terminal')
            ->where('is_test', $merchant->test_mode)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('pos.standalone.terminal', compact('merchant', 'terminals', 'currency', 'recentTransactions', 'user'));
    }

    /**
     * Process charge
     */
    public function charge(Request $request, string $merchantId)
    {
        $merchant = Merchant::findOrFail($merchantId);
        $user = Auth::user();

        if (!$user || $user->merchant_id !== $merchant->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.50',
            'terminal_id' => 'required|string',
            'description' => 'nullable|string|max:500',
        ]);

        $terminal = Terminal::where('merchant_id', $merchant->id)
            ->findOrFail($request->terminal_id);

        try {
            $paymentService = app(PaymentService::class);

            $transaction = $paymentService->createPayment($merchant, [
                'amount' => (int) round($request->amount * 100),
                'currency' => $merchant->default_currency ?? 'usd',
                'description' => $request->description ?? 'POS Payment',
                'payment_method_type' => 'terminal',
                'metadata' => [
                    'pos' => true,
                    'terminal_id' => $terminal->id,
                    'terminal_name' => $terminal->name,
                    'operator' => $user->name,
                ],
            ]);

            // Send to terminal
            $stripe = new \Stripe\StripeClient(
                $merchant->test_mode
                    ? config('services.stripe.test_secret')
                    : config('services.stripe.secret')
            );

            $reader = $stripe->terminal->readers->processPaymentIntent(
                $terminal->processor_terminal_id,
                ['payment_intent' => $transaction->processor_transaction_id]
            );

            return response()->json([
                'success' => true,
                'transaction' => [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'status' => $transaction->status,
                ],
                'reader_status' => $reader->action?->status ?? 'sent',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Check payment status
     */
    public function status(Request $request, string $merchantId, string $txnId)
    {
        $merchant = Merchant::findOrFail($merchantId);
        $transaction = Transaction::where('merchant_id', $merchant->id)->findOrFail($txnId);

        if ($transaction->status === 'pending' && $transaction->processor_transaction_id) {
            try {
                $stripe = new \Stripe\StripeClient(
                    $merchant->test_mode
                        ? config('services.stripe.test_secret')
                        : config('services.stripe.secret')
                );

                $intent = $stripe->paymentIntents->retrieve(
                    $transaction->processor_transaction_id
                );

                $newStatus = match ($intent->status) {
                    'succeeded' => 'succeeded',
                    'canceled' => 'canceled',
                    'requires_payment_method', 'requires_confirmation', 'requires_action' => 'pending',
                    default => $transaction->status,
                };

                if ($newStatus !== $transaction->status) {
                    $update = ['status' => $newStatus];
                    if ($newStatus === 'succeeded') {
                        $update['captured_at'] = now();
                        // Get card details
                        $charges = $intent->latest_charge
                            ? $stripe->charges->retrieve($intent->latest_charge)
                            : null;
                        if ($charges && $charges->payment_method_details?->card_present) {
                            $card = $charges->payment_method_details->card_present;
                            $update['card_brand'] = $card->brand ?? null;
                            $update['card_last4'] = $card->last4 ?? null;
                        }
                    }
                    $transaction->update($update);
                }
            } catch (\Exception $e) {
                // continue with DB status
            }
        }

        return response()->json([
            'status' => $transaction->status,
            'amount' => $transaction->amount,
            'card_brand' => $transaction->card_brand,
            'card_last4' => $transaction->card_last4,
        ]);
    }

    /**
     * Cancel payment
     */
    public function cancel(Request $request, string $merchantId, string $txnId)
    {
        $merchant = Merchant::findOrFail($merchantId);
        $transaction = Transaction::where('merchant_id', $merchant->id)->findOrFail($txnId);

        if (!in_array($transaction->status, ['pending', 'requires_action'])) {
            return response()->json(['success' => false, 'message' => 'Cannot cancel'], 400);
        }

        try {
            $stripe = new \Stripe\StripeClient(
                $merchant->test_mode
                    ? config('services.stripe.test_secret')
                    : config('services.stripe.secret')
            );

            if ($transaction->processor_transaction_id) {
                $stripe->paymentIntents->cancel($transaction->processor_transaction_id);
            }

            // Cancel reader action
            $terminal = Terminal::where('merchant_id', $merchant->id)->first();
            if ($terminal) {
                try {
                    $stripe->terminal->readers->cancelAction($terminal->processor_terminal_id);
                } catch (\Exception $e) {}
            }

            $transaction->update(['status' => 'canceled', 'failed_at' => now()]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request, string $merchantId)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('standalone.pos.login', $merchantId);
    }
}
