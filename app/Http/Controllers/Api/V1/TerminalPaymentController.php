<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Terminal;
use App\Models\Transaction;
use App\Services\Payment\PaymentService;
use App\Services\Payment\PaymentProcessorFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TerminalPaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create a connection token for the Terminal SDK.
     *
     * POST /v1/terminal/connection-token
     */
    public function connectionToken(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        try {
            $stripe = new \Stripe\StripeClient(
                $merchant->test_mode
                    ? config('services.stripe.test_secret')
                    : config('services.stripe.secret')
            );

            $token = $stripe->terminal->connectionTokens->create([], [
                'stripe_account' => $merchant->processor_account_id,
            ]);

            return response()->json([
                'secret' => $token->secret,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => ['type' => 'terminal_error', 'message' => $e->getMessage()]
            ], 400);
        }
    }

    /**
     * Create a payment intent for terminal use.
     *
     * POST /v1/terminal/create-payment
     */
    public function createPayment(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        $request->validate([
            'amount' => 'required|integer|min:50',
            'currency' => 'sometimes|string|size:3',
            'terminal_id' => 'sometimes|uuid',
            'description' => 'sometimes|string|max:500',
            'customer_id' => 'sometimes|uuid',
            'metadata' => 'sometimes|array',
        ]);

        try {
            $terminal = null;
            if ($terminalId = $request->input('terminal_id')) {
                $terminal = Terminal::where('merchant_id', $merchant->id)->find($terminalId);
            }

            $transaction = $this->paymentService->createPayment($merchant, [
                'amount' => $request->amount,
                'currency' => $request->currency ?? $merchant->default_currency ?? 'usd',
                'description' => $request->description ?? 'In-store payment',
                'payment_method_type' => 'terminal',
                'capture_method' => 'automatic',
                'source' => 'terminal',
                'terminal_id' => $terminal?->id,
                'customer_id' => $request->customer_id,
                'metadata' => array_merge($request->metadata ?? [], [
                    'terminal_id' => $terminal?->processor_terminal_id,
                ]),
            ]);

            return response()->json([
                'data' => [
                    'id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'client_secret' => $transaction->client_secret,
                    'payment_intent_id' => $transaction->processor_transaction_id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'status' => $transaction->status,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => ['type' => 'terminal_payment_error', 'message' => $e->getMessage()]
            ], 400);
        }
    }

    /**
     * Process payment on a specific reader (server-driven flow).
     *
     * POST /v1/terminal/process-payment
     *
     * This triggers the payment on the physical terminal using Stripe's
     * server-driven integration.
     */
    public function processPayment(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        $request->validate([
            'payment_intent_id' => 'required|string',
            'reader_id' => 'required|string',
        ]);

        try {
            $stripe = new \Stripe\StripeClient(
                $merchant->test_mode
                    ? config('services.stripe.test_secret')
                    : config('services.stripe.secret')
            );

            // Process payment on the reader
            $reader = $stripe->terminal->readers->processPaymentIntent(
                $request->reader_id,
                ['payment_intent' => $request->payment_intent_id],
                ['stripe_account' => $merchant->processor_account_id]
            );

            return response()->json([
                'data' => [
                    'reader_id' => $reader->id,
                    'status' => $reader->action->status ?? 'in_progress',
                    'action_type' => $reader->action->type ?? null,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => ['type' => 'terminal_process_error', 'message' => $e->getMessage()]
            ], 400);
        }
    }

    /**
     * Simulate a terminal payment (test mode only).
     *
     * POST /v1/terminal/simulate-payment
     */
    public function simulatePayment(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        if (!$merchant->test_mode) {
            return response()->json([
                'error' => ['type' => 'invalid_request', 'message' => 'Simulation only available in test mode.']
            ], 400);
        }

        $request->validate([
            'reader_id' => 'required|string',
        ]);

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.test_secret'));

            // Present a test card on the simulated reader
            $reader = $stripe->testHelpers->terminal->readers->presentPaymentMethod(
                $request->reader_id,
                [],
                ['stripe_account' => $merchant->processor_account_id]
            );

            return response()->json([
                'data' => [
                    'reader_id' => $reader->id,
                    'status' => $reader->action->status ?? 'succeeded',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => ['type' => 'simulation_error', 'message' => $e->getMessage()]
            ], 400);
        }
    }

    /**
     * List terminal readers for the merchant.
     *
     * GET /v1/terminal/readers
     */
    public function readers(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        $terminals = Terminal::where('merchant_id', $merchant->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'serial_number' => $t->serial_number,
                'model' => $t->model,
                'status' => $t->status,
                'location_name' => $t->location_name,
                'processor_terminal_id' => $t->processor_terminal_id,
                'battery_level' => $t->battery_level,
                'last_seen_at' => $t->last_seen_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $terminals]);
    }
}
