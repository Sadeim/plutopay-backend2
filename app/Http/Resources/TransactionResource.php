<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'type' => $this->type,
            'status' => $this->status,
            'status_badge' => $this->getStatusBadge(),

            // Amounts
            'amount' => $this->amount,
            'amount_formatted' => $this->formatAmount(),
            'amount_refunded' => $this->amount_refunded,
            'currency' => $this->currency,

            // Payment method
            'payment_method_type' => $this->payment_method_type,
            'card_brand' => $this->when($this->payment_method_type === 'card', $this->card_brand),
            'card_last_four' => $this->when($this->payment_method_type === 'card', $this->card_last_four),

            // Details
            'description' => $this->description,
            'receipt_email' => $this->receipt_email,
            'source' => $this->source,
            'is_test' => $this->is_test,

            // Relations
            'customer_id' => $this->customer_id,
            'terminal_id' => $this->terminal_id,

            // Processor (hidden from external API, visible for admin)
            'processor_type' => $this->when($this->shouldShowProcessorData($request), $this->processor_type),
            'processor_transaction_id' => $this->when($this->shouldShowProcessorData($request), $this->processor_transaction_id),

            // Metadata
            'metadata' => $this->metadata,

            // Timestamps
            'captured_at' => $this->captured_at?->toIso8601String(),
            'failed_at' => $this->failed_at?->toIso8601String(),
            'refunded_at' => $this->refunded_at?->toIso8601String(),
            'disputed_at' => $this->disputed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Format amount with currency symbol.
     */
    protected function formatAmount(): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'ILS' => '₪',
        ];

        $symbol = $symbols[$this->currency] ?? $this->currency . ' ';
        return $symbol . number_format($this->amount / 100, 2);
    }

    /**
     * Get status badge class for frontend.
     */
    protected function getStatusBadge(): string
    {
        return match ($this->status) {
            'succeeded' => 'success',
            'pending', 'processing' => 'warning',
            'failed' => 'danger',
            'refunded' => 'info',
            'canceled' => 'secondary',
            'disputed' => 'destructive',
            default => 'secondary',
        };
    }

    /**
     * Determine if processor data should be shown.
     * Only show for dashboard/internal requests, not external API.
     */
    protected function shouldShowProcessorData(Request $request): bool
    {
        // Show processor data for dashboard (session auth) requests
        // Hide for external API key auth requests
        return $request->hasHeader('X-Dashboard-Request') || $request->user()?->getMorphClass() !== 'merchant';
    }
}
