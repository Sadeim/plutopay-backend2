@extends('layouts.app')
@section('title', 'Transaction ' . $txn->reference)

@php
    $currencySymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'ILS' => '₪', 'AED' => 'د.إ', 'SAR' => '﷼', 'JOD' => 'د.ا'];
    $symbol = $currencySymbols[strtoupper($txn->currency)] ?? strtoupper($txn->currency) . ' ';
    $amount = $symbol . number_format($txn->amount / 100, 2);
    $amountRefunded = $symbol . number_format(($txn->amount_refunded ?? 0) / 100, 2);
    $remainingRefundable = $txn->amount - ($txn->amount_refunded ?? 0);

    $statusColors = [
        'succeeded' => 'success', 'pending' => 'warning', 'failed' => 'destructive',
        'refunded' => 'info', 'partially_refunded' => 'info',
        'disputed' => 'destructive', 'canceled' => 'secondary',
    ];
    $statusColor = $statusColors[$txn->status] ?? 'secondary';

    $methodIcons = [
        'card' => 'ki-filled ki-credit-cart', 'wallet' => 'ki-filled ki-wallet',
        'bank_transfer' => 'ki-filled ki-bank', 'terminal' => 'ki-filled ki-technology-4',
    ];
    $methodIcon = $methodIcons[$txn->payment_method_type] ?? 'ki-filled ki-dollar';
@endphp

@section('content')
    <div class="flex flex-col gap-5 lg:gap-7.5">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('dashboard.transactions.index') }}" class="text-secondary-foreground hover:text-foreground">
                <i class="ki-filled ki-arrow-left text-xs"></i> Back to Transactions
            </a>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="flex items-center gap-2 p-3 rounded-md bg-success/10 text-success text-sm">
            <i class="ki-filled ki-check-circle"></i> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="flex items-center gap-2 p-3 rounded-md bg-destructive/10 text-destructive text-sm">
            <i class="ki-filled ki-information-2"></i> {{ session('error') }}
        </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-wrap items-start gap-5 justify-between">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-semibold leading-none text-mono">{{ $amount }}</h1>
                    <span class="text-sm text-secondary-foreground font-mono">{{ strtoupper($txn->currency) }}</span>
                    <div class="kt-badge kt-badge-{{ $statusColor }} kt-badge-outline">
                        {{ ucfirst(str_replace('_', ' ', $txn->status)) }}
                    </div>
                </div>
                <div class="flex items-center gap-2 text-sm text-secondary-foreground">
                    <code class="font-mono">{{ $txn->reference }}</code>
                    <span>•</span>
                    <span>{{ $txn->created_at->format('M d, Y \a\t H:i') }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($txn->isRefundable())
                <button class="kt-btn kt-btn-sm kt-btn-outline" id="refundBtn">
                    <i class="ki-filled ki-arrow-left"></i> Refund
                </button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">

            {{-- Left Column --}}
            <div class="lg:col-span-2 flex flex-col gap-5">

                {{-- Payment Details --}}
                <div class="kt-card">
                    <div class="kt-card-content p-5 lg:p-7">
                        <h3 class="text-base font-semibold text-foreground mb-5">Payment Details</h3>
                        <div class="grid grid-cols-2 gap-y-4 gap-x-8">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Amount</span>
                                <span class="text-sm font-medium text-foreground">{{ $amount }}</span>
                            </div>
                            @if($txn->amount_refunded > 0)
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Refunded</span>
                                <span class="text-sm font-medium text-destructive">-{{ $amountRefunded }}</span>
                            </div>
                            @endif
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Status</span>
                                <div><span class="kt-badge kt-badge-sm kt-badge-{{ $statusColor }} kt-badge-outline">{{ ucfirst(str_replace('_', ' ', $txn->status)) }}</span></div>
                            </div>
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Type</span>
                                <span class="text-sm text-foreground">{{ ucfirst($txn->type ?? 'payment') }}</span>
                            </div>
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Source</span>
                                <span class="text-sm text-foreground">{{ ucfirst($txn->source ?? '-') }}</span>
                            </div>
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Test Mode</span>
                                <span class="text-sm text-foreground">{{ $txn->is_test ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="kt-card">
                    <div class="kt-card-content p-5 lg:p-7">
                        <h3 class="text-base font-semibold text-foreground mb-5">Payment Method</h3>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10">
                                <i class="{{ $methodIcon }} text-primary text-xl"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-foreground">{{ ucfirst(str_replace('_', ' ', $txn->payment_method_type ?? 'Unknown')) }}</span>
                                @if($txn->card_brand)
                                <span class="text-xs text-secondary-foreground">{{ ucfirst($txn->card_brand) }} ending in {{ $txn->card_last_four }}</span>
                                @endif
                            </div>
                        </div>
                        @if($txn->card_brand)
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-y-3 gap-x-8">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Brand</span>
                                <span class="text-sm text-foreground">{{ ucfirst($txn->card_brand) }}</span>
                            </div>
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Last 4</span>
                                <span class="text-sm font-mono text-foreground">•••• {{ $txn->card_last_four }}</span>
                            </div>
                            @if($txn->card_exp_month && $txn->card_exp_year)
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Expiry</span>
                                <span class="text-sm text-foreground">{{ str_pad($txn->card_exp_month, 2, '0', STR_PAD_LEFT) }}/{{ $txn->card_exp_year }}</span>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Refunds --}}
                @if($txn->refunds && $txn->refunds->count() > 0)
                <div class="kt-card">
                    <div class="kt-card-content p-5 lg:p-7">
                        <h3 class="text-base font-semibold text-foreground mb-4">Refunds</h3>
                        <div class="flex flex-col gap-3">
                            @foreach($txn->refunds as $refund)
                            <div class="flex items-center justify-between p-3 rounded-md bg-muted/50">
                                <div class="flex items-center gap-3">
                                    <i class="ki-filled ki-arrow-left text-info"></i>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-foreground">{{ $symbol }}{{ number_format($refund->amount / 100, 2) }}</span>
                                        <span class="text-xs text-secondary-foreground">{{ $refund->reason ?? 'No reason' }} • {{ $refund->created_at->format('M d, Y H:i') }}</span>
                                    </div>
                                </div>
                                <span class="kt-badge kt-badge-sm kt-badge-{{ $refund->status === 'succeeded' ? 'success' : 'warning' }} kt-badge-outline">
                                    {{ ucfirst($refund->status) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Failure Info --}}
                @if($txn->status === 'failed' && ($txn->failure_reason || $txn->failure_code))
                <div class="kt-card border-destructive/30">
                    <div class="kt-card-content p-5 lg:p-7">
                        <div class="flex items-start gap-3">
                            <i class="ki-filled ki-cross-circle text-destructive text-lg mt-0.5"></i>
                            <div class="flex flex-col gap-2">
                                <h3 class="text-base font-semibold text-destructive">Payment Failed</h3>
                                @if($txn->failure_code)
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-secondary-foreground">Error Code:</span>
                                    <code class="text-sm font-mono text-foreground">{{ $txn->failure_code }}</code>
                                </div>
                                @endif
                                @if($txn->failure_reason)
                                <p class="text-sm text-secondary-foreground">{{ $txn->failure_reason }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Description --}}
                @if($txn->description)
                <div class="kt-card">
                    <div class="kt-card-content p-5 lg:p-7">
                        <h3 class="text-base font-semibold text-foreground mb-3">Description</h3>
                        <p class="text-sm text-secondary-foreground">{{ $txn->description }}</p>
                    </div>
                </div>
                @endif

                {{-- Metadata --}}
                @if($txn->metadata)
                <div class="kt-card">
                    <div class="kt-card-content p-5 lg:p-7">
                        <h3 class="text-base font-semibold text-foreground mb-3">Metadata</h3>
                        <pre class="text-xs font-mono bg-muted rounded-md p-3 overflow-x-auto">{{ json_encode(is_string($txn->metadata) ? json_decode($txn->metadata, true) : $txn->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
                @endif
            </div>

            {{-- Right Column --}}
            <div class="flex flex-col gap-5">

                {{-- Timeline --}}
                <div class="kt-card">
                    <div class="kt-card-content p-5 lg:p-7">
                        <h3 class="text-base font-semibold text-foreground mb-5">Timeline</h3>
                        <div class="flex flex-col gap-4">
                            <div class="flex items-start gap-3">
                                <div class="flex items-center justify-center size-7 rounded-full bg-primary/10 shrink-0 mt-0.5">
                                    <span class="size-2 rounded-full bg-primary"></span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-foreground">Created</span>
                                    <span class="text-xs text-secondary-foreground">{{ $txn->created_at->format('M d, Y H:i:s') }}</span>
                                </div>
                            </div>
                            @if($txn->captured_at)
                            <div class="flex items-start gap-3">
                                <div class="flex items-center justify-center size-7 rounded-full shrink-0 mt-0.5" style="background-color:rgba(34,197,94,0.1)">
                                    <span class="size-2 rounded-full" style="background-color:#22c55e"></span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-foreground">Captured</span>
                                    <span class="text-xs text-secondary-foreground">{{ $txn->captured_at->format('M d, Y H:i:s') }}</span>
                                </div>
                            </div>
                            @endif
                            @if($txn->failed_at)
                            <div class="flex items-start gap-3">
                                <div class="flex items-center justify-center size-7 rounded-full shrink-0 mt-0.5" style="background-color:rgba(239,68,68,0.1)">
                                    <span class="size-2 rounded-full" style="background-color:#ef4444"></span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-foreground">Failed</span>
                                    <span class="text-xs text-secondary-foreground">{{ $txn->failed_at->format('M d, Y H:i:s') }}</span>
                                </div>
                            </div>
                            @endif
                            @if($txn->refunded_at)
                            <div class="flex items-start gap-3">
                                <div class="flex items-center justify-center size-7 rounded-full shrink-0 mt-0.5" style="background-color:rgba(14,165,233,0.1)">
                                    <span class="size-2 rounded-full" style="background-color:#0ea5e9"></span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-foreground">Refunded</span>
                                    <span class="text-xs text-secondary-foreground">{{ $txn->refunded_at->format('M d, Y H:i:s') }}</span>
                                </div>
                            </div>
                            @endif
                            @if($txn->disputed_at)
                            <div class="flex items-start gap-3">
                                <div class="flex items-center justify-center size-7 rounded-full shrink-0 mt-0.5" style="background-color:rgba(239,68,68,0.1)">
                                    <span class="size-2 rounded-full" style="background-color:#ef4444"></span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-foreground">Disputed</span>
                                    <span class="text-xs text-secondary-foreground">{{ $txn->disputed_at->format('M d, Y H:i:s') }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Customer --}}
                <div class="kt-card">
                    <div class="kt-card-content p-5 lg:p-7">
                        <h3 class="text-base font-semibold text-foreground mb-4">Customer</h3>
                        @if($txn->customer)
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex items-center justify-center size-10 rounded-full bg-primary/10 text-primary text-sm font-semibold">
                                {{ strtoupper(substr($txn->customer->name ?? '?', 0, 2)) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-foreground">{{ $txn->customer->name ?? '-' }}</span>
                                <span class="text-xs text-secondary-foreground">{{ $txn->customer->email ?? '-' }}</span>
                            </div>
                        </div>
                        @endif
                        @if($txn->receipt_email)
                        <div class="flex flex-col gap-0.5 mt-2">
                            <span class="text-xs text-secondary-foreground">Receipt Email</span>
                            <span class="text-sm text-foreground">{{ $txn->receipt_email }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- IDs --}}
                <div class="kt-card">
                    <div class="kt-card-content p-5 lg:p-7">
                        <h3 class="text-base font-semibold text-foreground mb-4">Reference IDs</h3>
                        <div class="flex flex-col gap-3">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Transaction ID</span>
                                <code class="text-xs font-mono text-foreground break-all">{{ $txn->id }}</code>
                            </div>
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Reference</span>
                                <code class="text-xs font-mono text-foreground">{{ $txn->reference }}</code>
                            </div>
                            @if($txn->processor_transaction_id)
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Processor ID</span>
                                <code class="text-xs font-mono text-foreground break-all">{{ $txn->processor_transaction_id }}</code>
                            </div>
                            @endif
                            @if($txn->idempotency_key)
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Idempotency Key</span>
                                <code class="text-xs font-mono text-foreground break-all">{{ $txn->idempotency_key }}</code>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Refund Modal --}}
    @if($txn->isRefundable())
    <div class="kt-modal" id="refundModal" data-kt-modal="true">
        <div class="kt-modal-content max-w-[450px] top-5 lg:top-[15%]">
            <div class="kt-modal-header">
                <h3 class="kt-modal-title">Refund Payment</h3>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light" data-kt-modal-dismiss="true">
                    <i class="ki-filled ki-cross"></i>
                </button>
            </div>
            <div class="kt-modal-body">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between p-3 rounded-md bg-muted">
                        <span class="text-sm text-secondary-foreground">Original Amount</span>
                        <span class="text-sm font-semibold text-foreground">{{ $amount }}</span>
                    </div>
                    @if($txn->amount_refunded > 0)
                    <div class="flex items-center justify-between p-3 rounded-md bg-muted">
                        <span class="text-sm text-secondary-foreground">Already Refunded</span>
                        <span class="text-sm font-semibold text-destructive">-{{ $amountRefunded }}</span>
                    </div>
                    @endif
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Refund Amount ({{ strtoupper($txn->currency) }})</label>
                        <input class="kt-input" id="refundAmount" type="number" step="0.01"
                               value="{{ number_format($remainingRefundable / 100, 2) }}"
                               max="{{ number_format($remainingRefundable / 100, 2) }}" min="0.01">
                        <span class="text-xs text-secondary-foreground">Max: {{ $symbol }}{{ number_format($remainingRefundable / 100, 2) }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Reason</label>
                        <select class="kt-select" id="refundReason">
                            <option value="requested_by_customer">Requested by customer</option>
                            <option value="duplicate">Duplicate</option>
                            <option value="fraudulent">Fraudulent</option>
                        </select>
                    </div>
                    <div id="refundError" class="hidden text-sm text-destructive bg-destructive/10 rounded-md p-3"></div>
                </div>
            </div>
            <div class="kt-modal-footer">
                <button class="kt-btn kt-btn-light" data-kt-modal-dismiss="true">Cancel</button>
                <button class="kt-btn kt-btn-primary" id="submitRefund">
                    <i class="ki-filled ki-arrow-left"></i> Process Refund
                </button>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const refundBtn = document.querySelector('#refundBtn');
    if (!refundBtn) return;

    const modalEl = document.querySelector('#refundModal');
    let modal = typeof KTModal !== 'undefined' ? KTModal.getInstance(modalEl) : null;

    refundBtn.addEventListener('click', function() {
        document.querySelector('#refundError').classList.add('hidden');
        if (modal) modal.show();
    });

    document.querySelector('#submitRefund').addEventListener('click', function() {
        const amountFloat = parseFloat(document.querySelector('#refundAmount').value);
        const reason = document.querySelector('#refundReason').value;
        const errorEl = document.querySelector('#refundError');
        const amountCents = Math.round(amountFloat * 100);
        const max = {{ $remainingRefundable }};

        if (isNaN(amountCents) || amountCents < 1) {
            errorEl.textContent = 'Please enter a valid amount.';
            errorEl.classList.remove('hidden');
            return;
        }
        if (amountCents > max) {
            errorEl.textContent = 'Amount exceeds refundable balance.';
            errorEl.classList.remove('hidden');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("dashboard.transactions.refund", $txn->id) }}';
        form.innerHTML = `
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="amount" value="${amountCents}">
            <input type="hidden" name="reason" value="${reason}">
        `;
        document.body.appendChild(form);
        form.submit();
    });
});
</script>
@endpush
