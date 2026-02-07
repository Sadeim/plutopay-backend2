<?php

namespace App\Services\Payment;

use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\Customer;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Create a payment intent (authorize or auto-capture).
     */
    public function createPayment(Merchant $merchant, array $data): Transaction
    {
        $processor = PaymentProcessorFactory::make($merchant);

        // Resolve customer if provided
        $customer = null;
        if (!empty($data['customer_id'])) {
            $customer = Customer::where('merchant_id', $merchant->id)
                ->find($data['customer_id']);
        }

        // Build processor params
        $processorData = [
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? $merchant->default_currency ?? 'usd',
            'description' => $data['description'] ?? null,
            'metadata' => array_merge($data['metadata'] ?? [], [
                'plutopay_merchant_id' => $merchant->id,
            ]),
            'capture_method' => ($data['capture_method'] ?? 'automatic') === 'manual' ? 'manual' : 'automatic',
            'payment_method_types' => ($data['payment_method_type'] ?? '') === 'terminal' ? ['card_present'] : null,
            'connected_account_id' => $merchant->processor_account_id,
            'application_fee_amount' => $merchant->processor_account_id
                ? (int) round(($data['amount']) * 3 / 100)
                : 0,
        ];

        if (!empty($data['payment_method'])) {
            $processorData['payment_method'] = $data['payment_method'];
            $processorData['confirm'] = $data['confirm'] ?? false;
        }

        if (!empty($data['return_url'])) {
            $processorData['return_url'] = $data['return_url'];
        }

        if ($customer && !empty($customer->processor_customer_id)) {
            $processorData['customer_processor_id'] = $customer->processor_customer_id;
        }

        // Create on Stripe
        $result = $processor->createPayment($processorData);

        // Extract card info from raw response
        $cardBrand = null;
        $cardLast4 = null;
        $cardExpMonth = null;
        $cardExpYear = null;
        $paymentMethodType = $data['payment_method_type'] ?? 'card';

        if (!empty($result['raw']['payment_method']) && is_string($result['raw']['payment_method'])) {
            // Payment method is just an ID, we'll get details from webhook
        } elseif (!empty($result['raw']['charges']['data'][0]['payment_method_details'])) {
            $pmDetails = $result['raw']['charges']['data'][0]['payment_method_details'];
            $paymentMethodType = $pmDetails['type'] ?? 'card';
            if (!empty($pmDetails['card'])) {
                $cardBrand = $pmDetails['card']['brand'] ?? null;
                $cardLast4 = $pmDetails['card']['last4'] ?? null;
                $cardExpMonth = $pmDetails['card']['exp_month'] ?? null;
                $cardExpYear = $pmDetails['card']['exp_year'] ?? null;
            }
        }

        // Determine status
        $status = $result['status'] ?? 'pending';
        $capturedAt = $status === 'succeeded' ? now() : null;

        // Create transaction record
        $transaction = Transaction::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $customer?->id,
            'reference' => 'txn_' . Str::random(20),
            'type' => 'payment',
            'status' => $status,
            'amount' => $data['amount'],
            'amount_refunded' => 0,
            'currency' => strtolower($data['currency'] ?? $merchant->default_currency ?? 'usd'),
            'payment_method_type' => $paymentMethodType,
            'card_brand' => $cardBrand,
            'card_last_four' => $cardLast4,
            'card_exp_month' => $cardExpMonth,
            'card_exp_year' => $cardExpYear,
            'source' => $data['source'] ?? 'api',
            'terminal_id' => $data['terminal_id'] ?? null,
            'processor_type' => $merchant->processor_type,
            'processor_transaction_id' => $result['processor_id'],
            'idempotency_key' => $data['idempotency_key'] ?? null,
            'description' => $data['description'] ?? null,
            'receipt_email' => $data['receipt_email'] ?? null,
            'billing_address' => $data['billing_address'] ?? null,
            'shipping_address' => $data['shipping_address'] ?? null,
            'is_test' => $merchant->test_mode,
            'metadata' => $data['metadata'] ?? null,
            'captured_at' => $capturedAt,
        ]);

        // Attach client_secret for frontend confirmation
        $transaction->client_secret = $result['client_secret'] ?? null;

        return $transaction;
    }

    /**
     * Capture an authorized payment.
     */
    public function capturePayment(Merchant $merchant, Transaction $transaction, ?int $amount = null): Transaction
    {
        if ($transaction->status !== 'authorized') {
            throw new \Exception('Payment is not in authorized state.');
        }

        $processor = PaymentProcessorFactory::make($merchant);

        $captureData = [];
        if ($amount !== null) {
            $captureData['amount_to_capture'] = $amount;
        }

        $result = $processor->capturePayment(
            $transaction->processor_transaction_id,
            $captureData
        );

        $transaction->update([
            'status' => $result['status'] ?? 'succeeded',
            'captured_at' => now(),
        ]);

        return $transaction->fresh();
    }

    /**
     * Cancel a pending/authorized payment.
     */
    public function cancelPayment(Merchant $merchant, Transaction $transaction): Transaction
    {
        if (!in_array($transaction->status, ['pending', 'authorized'])) {
            throw new \Exception('Payment cannot be canceled in current state.');
        }

        $processor = PaymentProcessorFactory::make($merchant);
        $processor->cancelPayment($transaction->processor_transaction_id);

        $transaction->update([
            'status' => 'canceled',
        ]);

        return $transaction->fresh();
    }
}
