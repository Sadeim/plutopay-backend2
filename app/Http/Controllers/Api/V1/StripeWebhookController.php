<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\Payout;
use App\Models\Dispute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    /**
     * Handle incoming Stripe webhook events.
     *
     * POST /api/v1/stripe/webhook
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        if (!$sigHeader || !$secret) {
            Log::warning('Stripe webhook: missing signature or secret');
            return response()->json(['error' => 'Missing signature'], 400);
        }

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook: invalid signature', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::error('Stripe webhook: parse error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Parse error'], 400);
        }

        Log::info('Stripe webhook received', ['type' => $event->type, 'id' => $event->id]);

        match ($event->type) {
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($event->data->object),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event->data->object),
            'payment_intent.canceled' => $this->handlePaymentCanceled($event->data->object),
            'charge.refunded' => $this->handleChargeRefunded($event->data->object),
            'charge.dispute.created' => $this->handleDisputeCreated($event->data->object),
            'payout.created' => $this->handlePayoutCreatedOrUpdated($event->data->object),
            'payout.updated' => $this->handlePayoutCreatedOrUpdated($event->data->object),
            'payout.paid' => $this->handlePayoutPaid($event->data->object),
            'payout.failed' => $this->handlePayoutFailed($event->data->object),
            default => Log::info("Stripe webhook: unhandled event type {$event->type}"),
        };

        return response()->json(['received' => true], 200);
    }

    /**
     * Handle Connect account webhooks.
     *
     * POST /api/v1/stripe/webhook/connect
     */
    public function handleConnect(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.connect_webhook_secret');

        if (!$sigHeader || !$secret) {
            return response()->json(['error' => 'Missing signature'], 400);
        }

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            Log::warning('Stripe Connect webhook: invalid', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $connectedAccountId = $event->account ?? null;

        Log::info('Stripe Connect webhook', [
            'type' => $event->type,
            'account' => $connectedAccountId,
        ]);

        if (!$connectedAccountId) {
            return response()->json(['received' => true], 200);
        }

        // Find merchant by processor_account_id
        $merchant = Merchant::where('processor_account_id', $connectedAccountId)->first();

        if (!$merchant) {
            Log::warning("Stripe Connect webhook: no merchant for account {$connectedAccountId}");
            return response()->json(['received' => true], 200);
        }

        match ($event->type) {
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($event->data->object, $merchant),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event->data->object, $merchant),
            'payment_intent.canceled' => $this->handlePaymentCanceled($event->data->object, $merchant),
            'charge.refunded' => $this->handleChargeRefunded($event->data->object, $merchant),
            'charge.dispute.created' => $this->handleDisputeCreated($event->data->object, $merchant),
            'payout.created' => $this->handlePayoutCreatedOrUpdated($event->data->object, $merchant),
            'payout.updated' => $this->handlePayoutCreatedOrUpdated($event->data->object, $merchant),
            'payout.paid' => $this->handlePayoutPaid($event->data->object, $merchant),
            'payout.failed' => $this->handlePayoutFailed($event->data->object, $merchant),
            default => null,
        };

        return response()->json(['received' => true], 200);
    }

    // ── Event Handlers ──

    protected function handlePaymentSucceeded(object $intent, ?Merchant $merchant = null): void
    {
        $txn = $this->findTransaction($intent->id, $merchant);
        if (!$txn) return;

        $updateData = [
            'status' => 'succeeded',
            'captured_at' => now(),
        ];

        // Extract card info if available
        if (!empty($intent->charges->data[0]->payment_method_details)) {
            $pm = $intent->charges->data[0]->payment_method_details;
            if (($pm->type ?? null) === 'card' && !empty($pm->card)) {
                $updateData['card_brand'] = $pm->card->brand ?? $txn->card_brand;
                $updateData['card_last_four'] = $pm->card->last4 ?? $txn->card_last_four;
                $updateData['card_exp_month'] = $pm->card->exp_month ?? $txn->card_exp_month;
                $updateData['card_exp_year'] = $pm->card->exp_year ?? $txn->card_exp_year;
                $updateData['payment_method_type'] = 'card';
            } elseif (($pm->type ?? null) === 'card_present') {
                $updateData['payment_method_type'] = 'terminal';
                if (!empty($pm->card_present)) {
                    $updateData['card_brand'] = $pm->card_present->brand ?? $txn->card_brand;
                    $updateData['card_last_four'] = $pm->card_present->last4 ?? $txn->card_last_four;
                }
            }
        }

        // Tip amount
        $tipAmount = $intent->amount_details->tip->amount ?? 0;
        if ($tipAmount > 0) {
            $updateData['tip_amount'] = $tipAmount;
        }

        // Receipt URL
        if (!empty($intent->charges->data[0]->receipt_url)) {
            $updateData['receipt_url'] = $intent->charges->data[0]->receipt_url;
        }

        $txn->update($updateData);

        Log::info("Payment succeeded: {$txn->reference}");
    }

    protected function handlePaymentFailed(object $intent, ?Merchant $merchant = null): void
    {
        $txn = $this->findTransaction($intent->id, $merchant);
        if (!$txn) return;

        $failureMessage = $intent->last_payment_error->message ?? 'Payment failed';
        $failureCode = $intent->last_payment_error->code ?? null;

        $txn->update([
            'status' => 'failed',
            'failure_reason' => $failureMessage,
            'failure_code' => $failureCode,
            'failed_at' => now(),
        ]);

        Log::info("Payment failed: {$txn->reference} - {$failureMessage}");
    }

    protected function handlePaymentCanceled(object $intent, ?Merchant $merchant = null): void
    {
        $txn = $this->findTransaction($intent->id, $merchant);
        if (!$txn) return;

        $txn->update(['status' => 'canceled']);
        Log::info("Payment canceled: {$txn->reference}");
    }

    protected function handleChargeRefunded(object $charge, ?Merchant $merchant = null): void
    {
        $intentId = $charge->payment_intent;
        if (!$intentId) return;

        $txn = $this->findTransaction($intentId, $merchant);
        if (!$txn) return;

        $amountRefunded = $charge->amount_refunded;
        $txn->update([
            'amount_refunded' => $amountRefunded,
            'status' => $amountRefunded >= $txn->amount ? 'refunded' : 'partially_refunded',
            'refunded_at' => now(),
        ]);

        Log::info("Charge refunded: {$txn->reference}, amount: {$amountRefunded}");
    }

    protected function handleDisputeCreated(object $dispute, ?Merchant $merchant = null): void
    {
        $chargeId = $dispute->charge;
        Log::info("Dispute created for charge: {$chargeId}", ['reason' => $dispute->reason ?? 'unknown']);
    }


    protected function handlePayoutCreatedOrUpdated(object $payout, ?Merchant $merchant = null): void
    {
        if (!$merchant) return;

        $existing = Payout::where('merchant_id', $merchant->id)
            ->where('processor_payout_id', $payout->id)
            ->first();

        $status = match($payout->status) {
            'paid' => 'paid',
            'pending' => 'pending',
            'in_transit' => 'in_transit',
            'canceled' => 'canceled',
            'failed' => 'failed',
            default => 'pending',
        };

        $arrivalDate = isset($payout->arrival_date)
            ? \Carbon\Carbon::createFromTimestamp($payout->arrival_date)
            : null;

        if ($existing) {
            $existing->update([
                'status' => $status,
                'amount' => $payout->amount,
                'estimated_arrival_at' => $arrivalDate,
                'arrived_at' => $status === 'paid' ? $arrivalDate : $existing->arrived_at,
            ]);
            Log::info("Payout updated: {$payout->id} -> {$status}");
        } else {
            Payout::create([
                'merchant_id' => $merchant->id,
                'reference' => 'po_' . \Illuminate\Support\Str::random(20),
                'amount' => $payout->amount,
                'fee' => 0,
                'net_amount' => $payout->amount,
                'currency' => strtoupper($payout->currency),
                'status' => $status,
                'destination_type' => $payout->type ?? 'bank_account',
                'destination_last_four' => is_string($payout->destination) ? substr($payout->destination, -4) : null,
                'processor_payout_id' => $payout->id,
                'is_test' => $merchant->test_mode,
                'estimated_arrival_at' => $arrivalDate,
                'arrived_at' => $status === 'paid' ? $arrivalDate : null,
                'failed_at' => $status === 'failed' ? now() : null,
                'failure_reason' => $payout->failure_message ?? null,
            ]);
            Log::info("Payout created from webhook: {$payout->id}");
        }
    }

    protected function handlePayoutPaid(object $payout, ?Merchant $merchant = null): void
    {
        if (!$merchant) return;

        $existing = Payout::where('merchant_id', $merchant->id)
            ->where('processor_payout_id', $payout->id)
            ->first();

        if ($existing) {
            $existing->update([
                'status' => 'paid',
                'arrived_at' => now(),
            ]);
        } else {
            $this->handlePayoutCreatedOrUpdated($payout, $merchant);
        }

        Log::info("Payout paid: {$payout->id}");
    }

    protected function handlePayoutFailed(object $payout, ?Merchant $merchant = null): void
    {
        if (!$merchant) return;

        $existing = Payout::where('merchant_id', $merchant->id)
            ->where('processor_payout_id', $payout->id)
            ->first();

        if ($existing) {
            $existing->update([
                'status' => 'failed',
                'failure_reason' => $payout->failure_message ?? 'Payout failed',
                'failed_at' => now(),
            ]);
        } else {
            $this->handlePayoutCreatedOrUpdated($payout, $merchant);
        }

        Log::info("Payout failed: {$payout->id}");
    }

    // ── Helpers ──

    protected function findTransaction(string $processorId, ?Merchant $merchant = null): ?Transaction
    {
        $query = Transaction::where('processor_transaction_id', $processorId);

        if ($merchant) {
            $query->where('merchant_id', $merchant->id);
        }

        return $query->first();
    }
}
