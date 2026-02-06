<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\Payment\PaymentProcessorFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        $transactions = Transaction::where('merchant_id', $merchant->id)
            ->where('is_test', $request->input('is_test', $merchant->test_mode))
            ->when($request->input('status'), fn($q, $s) => $q->where('status', $s))
            ->when($request->input('type'), fn($q, $t) => $q->where('type', $t))
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json($transactions);
    }

    public function show(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');

        $transaction = Transaction::where('merchant_id', $merchant->id)
            ->findOrFail($id);

        return response()->json($transaction);
    }

    public function store(Request $request)
    {
        $merchant = $request->attributes->get('merchant');
        $isTest = $request->input('is_test', $merchant->test_mode);

        $request->validate([
            'amount' => 'required|integer|min:1',
            'currency' => 'sometimes|string|size:3',
            'payment_method' => 'sometimes|string',
            'customer_id' => 'sometimes|uuid',
            'description' => 'sometimes|string|max:500',
            'metadata' => 'sometimes|array',
            'idempotency_key' => 'sometimes|string|max:255',
        ]);

        // Idempotency check
        if ($request->idempotency_key) {
            $existing = Transaction::where('merchant_id', $merchant->id)
                ->where('idempotency_key', $request->idempotency_key)
                ->first();
            if ($existing) return response()->json($existing);
        }

        // Create via processor
        $processor = PaymentProcessorFactory::make($merchant);

        try {
            $result = $processor->createPayment([
                'amount' => $request->amount,
                'currency' => $request->input('currency', $merchant->default_currency),
                'payment_method' => $request->payment_method,
                'description' => $request->description,
                'metadata' => $request->input('metadata', []),
                'confirm' => $request->boolean('confirm', false),
            ]);

            $transaction = Transaction::create([
                'merchant_id' => $merchant->id,
                'customer_id' => $request->customer_id,
                'reference' => 'txn_' . Str::random(24),
                'type' => 'payment',
                'status' => $result['status'],
                'amount' => $request->amount,
                'currency' => $request->input('currency', $merchant->default_currency),
                'payment_method_type' => $request->payment_method ? 'card' : null,
                'source' => 'api',
                'processor_type' => $merchant->processor_type,
                'processor_transaction_id' => $result['processor_id'],
                'processor_response' => $result['raw'] ?? null,
                'idempotency_key' => $request->idempotency_key,
                'description' => $request->description,
                'is_test' => $isTest,
                'metadata' => $request->input('metadata'),
                'captured_at' => $result['status'] === 'succeeded' ? now() : null,
            ]);

            return response()->json($transaction, 201);

        } catch (\Exception $e) {
            $transaction = Transaction::create([
                'merchant_id' => $merchant->id,
                'reference' => 'txn_' . Str::random(24),
                'type' => 'payment',
                'status' => 'failed',
                'amount' => $request->amount,
                'currency' => $request->input('currency', $merchant->default_currency),
                'source' => 'api',
                'processor_type' => $merchant->processor_type,
                'failure_reason' => $e->getMessage(),
                'is_test' => $isTest,
                'idempotency_key' => $request->idempotency_key,
                'failed_at' => now(),
            ]);

            return response()->json([
                'error' => ['type' => 'payment_error', 'message' => $e->getMessage()],
                'transaction' => $transaction,
            ], 400);
        }
    }
}
