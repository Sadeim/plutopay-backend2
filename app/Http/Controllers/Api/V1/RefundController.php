<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\Transaction;
use App\Services\Payment\PaymentProcessorFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RefundController extends Controller
{
    public function store(Request $request, string $transactionId)
    {
        $merchant = $request->attributes->get('merchant');

        $transaction = Transaction::where('merchant_id', $merchant->id)->findOrFail($transactionId);

        if (!$transaction->isRefundable()) {
            return response()->json([
                'error' => ['type' => 'invalid_request', 'message' => 'Transaction is not refundable.']
            ], 400);
        }

        $request->validate([
            'amount' => 'sometimes|integer|min:1',
            'reason' => 'sometimes|string|max:500',
        ]);

        $amount = $request->input('amount', $transaction->remaining_refundable);

        if ($amount > $transaction->remaining_refundable) {
            return response()->json([
                'error' => ['type' => 'invalid_request', 'message' => 'Refund amount exceeds refundable amount.']
            ], 400);
        }

        $processor = PaymentProcessorFactory::make($merchant);

        try {
            $result = $processor->createRefund($transaction->processor_transaction_id, [
                'amount' => $amount,
                'reason' => $request->reason,
            ]);

            $refund = Refund::create([
                'merchant_id' => $merchant->id,
                'transaction_id' => $transaction->id,
                'reference' => 'ref_' . Str::random(24),
                'amount' => $amount,
                'currency' => $transaction->currency,
                'status' => $result['status'],
                'reason' => $request->reason,
                'processor_refund_id' => $result['processor_id'],
                'processor_response' => $result['raw'] ?? null,
                'is_test' => $transaction->is_test,
            ]);

            $transaction->update([
                'amount_refunded' => $transaction->amount_refunded + $amount,
                'refunded_at' => now(),
                'status' => ($transaction->amount_refunded + $amount >= $transaction->amount) ? 'refunded' : 'partially_refunded',
            ]);

            return response()->json($refund, 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => ['type' => 'refund_error', 'message' => $e->getMessage()]
            ], 400);
        }
    }
}
