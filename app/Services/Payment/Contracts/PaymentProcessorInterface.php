<?php

namespace App\Services\Payment\Contracts;

interface PaymentProcessorInterface
{
    // Payments
    public function createPayment(array $data): array;
    public function capturePayment(string $processorId, array $data = []): array;
    public function cancelPayment(string $processorId): array;

    // Refunds
    public function createRefund(string $processorId, array $data): array;

    // Customers
    public function createCustomer(array $data): array;
    public function updateCustomer(string $processorId, array $data): array;
    public function deleteCustomer(string $processorId): array;

    // Payouts
    public function createPayout(array $data): array;

    // Terminals
    public function registerTerminal(array $data): array;
    public function createTerminalConnectionToken(string $terminalId): array;

    // Accounts (for Connect / marketplace)
    public function createAccount(array $data): array;
    public function updateAccount(string $accountId, array $data): array;
    public function getAccount(string $accountId): array;

    // Webhooks
    public function constructWebhookEvent(string $payload, string $signature, string $secret): array;
}
