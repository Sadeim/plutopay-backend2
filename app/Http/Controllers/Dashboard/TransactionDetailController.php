<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Refund;
use App\Services\Payment\PaymentProcessorFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransactionDetailController extends Controller
{
    public function show(string $id)
    {
        $merchant = auth()->user()->merchant;

        $transaction = Transaction::where('merchant_id', $merchant->id)
            ->with(['customer', 'refunds'])
            ->findOrFail($id);

        return view('dashboard.transactions.show', [
            'txn' => $transaction,
        ]);
    }

    public function refund(Request $request, string $id)
    {
        $merchant = auth()->user()->merchant;

        $transaction = Transaction::where('merchant_id', $merchant->id)->findOrFail($id);

        if (!$transaction->isRefundable()) {
            return back()->with('error', 'This transaction is not refundable.');
        }

        $request->validate([
            'amount' => 'required|integer|min:1',
            'reason' => 'required|in:requested_by_customer,duplicate,fraudulent',
        ]);

        $amount = $request->amount;
        $remaining = $transaction->amount - ($transaction->amount_refunded ?? 0);

        if ($amount > $remaining) {
            return back()->with('error', 'Refund amount exceeds refundable balance.');
        }

        try {
            $processor = PaymentProcessorFactory::make($merchant);

            $result = $processor->createRefund($transaction->processor_transaction_id, [
                'amount' => $amount,
                'reason' => $request->reason,
            ]);

            Refund::create([
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

            $newRefunded = ($transaction->amount_refunded ?? 0) + $amount;
            $transaction->update([
                'amount_refunded' => $newRefunded,
                'refunded_at' => now(),
                'status' => $newRefunded >= $transaction->amount ? 'refunded' : 'partially_refunded',
            ]);

            $formatted = number_format($amount / 100, 2);
            return back()->with('success', "Refund of \${$formatted} processed successfully!");

        } catch (\Exception $e) {
            return back()->with('error', 'Refund failed: ' . $e->getMessage());
        }
    }
}
