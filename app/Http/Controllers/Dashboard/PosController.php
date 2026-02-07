<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Terminal;
use App\Models\Transaction;
use App\Services\Payment\PaymentService;
use App\Services\Payment\PaymentProcessorFactory;
use Illuminate\Http\Request;

class PosController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $merchant = auth()->user()->merchant;

        $terminals = Terminal::where('merchant_id', $merchant->id)
            ->orderByDesc('status')
            ->get();

        $recentTransactions = Transaction::where('merchant_id', $merchant->id)
            ->where('source', 'terminal')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboard.pos.index', [
            'terminals' => $terminals,
            'recentTransactions' => $recentTransactions,
            'currency' => $merchant->default_currency ?? 'USD',
            'merchant' => $merchant,
        ]);
    }

    /**
     * Create payment and send to terminal.
     */
    public function charge(Request $request)
    {
        $merchant = auth()->user()->merchant;

        $request->validate([
            'amount' => 'required|numeric|min:0.50',
            'terminal_id' => 'required|exists:terminals,id',
            'description' => 'nullable|string|max:500',
        ]);

        $terminal = Terminal::where('merchant_id', $merchant->id)
            ->findOrFail($request->terminal_id);

        $amountCents = (int) round($request->amount * 100);

        try {
            // 1. Create Payment Intent
            $transaction = $this->paymentService->createPayment($merchant, [
                'amount' => $amountCents,
                'currency' => strtolower($merchant->default_currency ?? 'usd'),
                'description' => $request->description ?: 'POS Payment',
                'payment_method_type' => 'terminal',
                'source' => 'terminal',
                'terminal_id' => $terminal->id,
                'metadata' => [
                    'pos' => true,
                    'terminal_name' => $terminal->name,
                ],
            ]);

            // 2. Send to terminal reader (server-driven)
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
                    'reference' => $transaction->reference,
                    'amount' => $amountCents,
                    'status' => 'waiting_for_reader',
                    'reader_status' => $reader->action->status ?? 'in_progress',
                ],
                'message' => 'Payment sent to terminal. Waiting for customer...',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Check payment status.
     */
    public function status(Request $request, string $id)
    {
        $merchant = auth()->user()->merchant;
        $transaction = Transaction::where('merchant_id', $merchant->id)->findOrFail($id);

        // If still pending, check Stripe directly
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

                $newStatus = match($intent->status) {
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
                        if (isset($intent->charges->data[0]->payment_method_details)) {
                            $pm = $intent->charges->data[0]->payment_method_details;
                            if (isset($pm->card_present)) {
                                $update['card_brand'] = $pm->card_present->brand ?? null;
                                $update['card_last_four'] = $pm->card_present->last4 ?? null;
                                $update['payment_method_type'] = 'terminal';
                            }
                        }
                        if (isset($intent->charges->data[0]->receipt_url)) {
                            $update['receipt_url'] = $intent->charges->data[0]->receipt_url;
                        }
                    }
                    $transaction->update($update);
                }
            } catch (\Exception $e) {
                // Ignore - return DB status
            }
        }

        return response()->json([
            'id' => $transaction->id,
            'reference' => $transaction->reference,
            'status' => $transaction->status,
            'amount' => $transaction->amount,
        ]);
    }
    /**
     * Cancel a pending payment
     */
    public function cancel(Request $request, string $id)
    {
        $merchant = auth()->user()->merchant;
        $transaction = \App\Models\Transaction::where('merchant_id', $merchant->id)->findOrFail($id);

        if (!in_array($transaction->status, ['pending', 'requires_action'])) {
            return response()->json(['success' => false, 'message' => 'Cannot cancel this payment'], 400);
        }

        try {
            $stripe = new \Stripe\StripeClient(
                $merchant->test_mode
                    ? config('services.stripe.test_secret')
                    : config('services.stripe.secret')
            );

            // Cancel on Stripe
            if ($transaction->processor_transaction_id) {
                $stripe->paymentIntents->cancel($transaction->processor_transaction_id);
            }

            // Cancel reader action
            $terminal = \App\Models\Terminal::where('merchant_id', $merchant->id)->first();
            if ($terminal && $terminal->processor_terminal_id) {
                try {
                    $stripe->terminal->readers->cancelAction($terminal->processor_terminal_id);
                } catch (\Exception $e) {
                    // Reader might not have active action
                }
            }

            $transaction->update([
                'status' => 'canceled',
                'failed_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Payment cancelled']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

}
