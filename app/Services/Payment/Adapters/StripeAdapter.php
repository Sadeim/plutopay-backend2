<?php

namespace App\Services\Payment\Adapters;

use App\Services\Payment\Contracts\PaymentProcessorInterface;

class StripeAdapter implements PaymentProcessorInterface
{
    private $stripe;

    public function __construct(?string $secretKey = null)
    {
        $this->stripe = new \Stripe\StripeClient($secretKey ?? config('services.stripe.secret'));
    }

    // ── Payments ──

    public function createPayment(array $data): array
    {
        $params = [
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'usd',
            'payment_method' => $data['payment_method'] ?? null,
            'confirm' => $data['confirm'] ?? false,
            'description' => $data['description'] ?? null,
            
            'metadata' => $data['metadata'] ?? [],
        ];

        if (isset($data['customer_processor_id'])) {
            $params['customer'] = $data['customer_processor_id'];
        }
        if (isset($data['return_url'])) {
            $params['return_url'] = $data['return_url'];
        }
        // Payment method types: card_present for terminal, automatic for online
        if (isset($data['payment_method_types']) && is_array($data['payment_method_types'])) {
            $params['payment_method_types'] = $data['payment_method_types'];
        } else {
            $params['automatic_payment_methods'] = ['enabled' => true, 'allow_redirects' => 'never'];
        }
        if (isset($data['capture_method'])) {
            $params['capture_method'] = $data['capture_method'];
        }
        if (isset($data['stripe_account'])) {
            $params['stripe_account'] = $data['stripe_account'];
        }

        $intent = $this->stripe->paymentIntents->create(array_filter($params));

        return [
            'processor_id' => $intent->id,
            'status' => $this->mapPaymentStatus($intent->status),
            'client_secret' => $intent->client_secret,
            'amount' => $intent->amount,
            'currency' => $intent->currency,
            'payment_method' => $intent->payment_method,
            'raw' => $intent->toArray(),
        ];
    }

    public function capturePayment(string $processorId, array $data = []): array
    {
        $intent = $this->stripe->paymentIntents->capture($processorId, $data);
        return [
            'processor_id' => $intent->id,
            'status' => $this->mapPaymentStatus($intent->status),
            'raw' => $intent->toArray(),
        ];
    }

    public function cancelPayment(string $processorId): array
    {
        $intent = $this->stripe->paymentIntents->cancel($processorId);
        return [
            'processor_id' => $intent->id,
            'status' => 'cancelled',
            'raw' => $intent->toArray(),
        ];
    }

    // ── Refunds ──

    public function createRefund(string $processorId, array $data): array
    {
        $refund = $this->stripe->refunds->create([
            'payment_intent' => $processorId,
            'amount' => $data['amount'] ?? null,
            'reason' => $data['reason'] ?? null,
            'metadata' => $data['metadata'] ?? [],
        ]);

        return [
            'processor_id' => $refund->id,
            'status' => $this->mapRefundStatus($refund->status),
            'amount' => $refund->amount,
            'raw' => $refund->toArray(),
        ];
    }

    // ── Customers ──

    public function createCustomer(array $data): array
    {
        $customer = $this->stripe->customers->create([
            'email' => $data['email'] ?? null,
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'metadata' => $data['metadata'] ?? [],
        ]);

        return [
            'processor_id' => $customer->id,
            'raw' => $customer->toArray(),
        ];
    }

    public function updateCustomer(string $processorId, array $data): array
    {
        $customer = $this->stripe->customers->update($processorId, array_filter([
            'email' => $data['email'] ?? null,
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]));

        return ['processor_id' => $customer->id, 'raw' => $customer->toArray()];
    }

    public function deleteCustomer(string $processorId): array
    {
        $customer = $this->stripe->customers->delete($processorId);
        return ['processor_id' => $customer->id, 'deleted' => true];
    }

    // ── Payouts ──

    public function createPayout(array $data): array
    {
        $payout = $this->stripe->payouts->create([
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'usd',
            'metadata' => $data['metadata'] ?? [],
        ]);

        return [
            'processor_id' => $payout->id,
            'status' => $payout->status,
            'amount' => $payout->amount,
            'arrival_date' => $payout->arrival_date,
            'raw' => $payout->toArray(),
        ];
    }

    // ── Terminals ──

    public function registerTerminal(array $data): array
    {
        $terminal = $this->stripe->terminal->readers->create([
            'registration_code' => $data['registration_code'],
            'label' => $data['label'] ?? null,
            'location' => $data['location_id'] ?? null,
        ]);

        return [
            'processor_id' => $terminal->id,
            'serial_number' => $terminal->serial_number,
            'status' => $terminal->status,
            'raw' => $terminal->toArray(),
        ];
    }

    public function createTerminalConnectionToken(string $terminalId): array
    {
        $token = $this->stripe->terminal->connectionTokens->create([
            'location' => $terminalId,
        ]);

        return ['secret' => $token->secret];
    }

    // ── Accounts ──

    public function createAccount(array $data): array
    {
        $account = $this->stripe->accounts->create([
            'type' => 'express',
            'email' => $data['email'] ?? null,
            'business_type' => $data['business_type'] ?? 'individual',
            'metadata' => $data['metadata'] ?? [],
        ]);

        return ['processor_id' => $account->id, 'raw' => $account->toArray()];
    }

    public function updateAccount(string $accountId, array $data): array
    {
        $account = $this->stripe->accounts->update($accountId, $data);
        return ['processor_id' => $account->id, 'raw' => $account->toArray()];
    }

    public function getAccount(string $accountId): array
    {
        $account = $this->stripe->accounts->retrieve($accountId);
        return ['processor_id' => $account->id, 'raw' => $account->toArray()];
    }

    // ── Webhooks ──

    public function constructWebhookEvent(string $payload, string $signature, string $secret): array
    {
        $event = \Stripe\Webhook::constructEvent($payload, $signature, $secret);
        return [
            'id' => $event->id,
            'type' => $event->type,
            'data' => $event->data->toArray(),
            'raw' => $event->toArray(),
        ];
    }

    // ── Status Mappers ──

    private function mapPaymentStatus(string $stripeStatus): string
    {
        return match ($stripeStatus) {
            'succeeded' => 'succeeded',
            'processing' => 'processing',
            'requires_payment_method', 'requires_confirmation', 'requires_action' => 'pending',
            'requires_capture' => 'authorized',
            'canceled' => 'cancelled',
            default => 'failed',
        };
    }

    private function mapRefundStatus(string $stripeStatus): string
    {
        return match ($stripeStatus) {
            'succeeded' => 'succeeded',
            'pending' => 'pending',
            'failed' => 'failed',
            default => 'pending',
        };
    }
}
