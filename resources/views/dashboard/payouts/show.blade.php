@extends('layouts.app')
@section('title', 'Payout Details')

@php
    $currencySymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'ILS' => '₪', 'AED' => 'د.إ', 'SAR' => '﷼', 'JOD' => 'د.ا'];
    $symbol = $currencySymbols[$currency] ?? $currency . ' ';
    $amount = $symbol . number_format($payout->amount / 100, 2);
@endphp

@section('content')
    <div class="flex flex-col gap-5 lg:gap-7.5">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('dashboard.payouts.index') }}" class="text-secondary-foreground hover:text-foreground">
                <i class="ki-filled ki-arrow-left text-xs"></i> Back to Payouts
            </a>
        </div>

        {{-- Header --}}
        <div class="flex flex-wrap items-start gap-5 justify-between">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-semibold leading-none text-mono">{{ $amount }}</h1>
                    <span class="text-sm text-secondary-foreground font-mono">{{ strtoupper($payout->currency) }}</span>
                    @if($payout->status === 'paid')
                    <div class="kt-badge kt-badge-success kt-badge-outline">Paid</div>
                    @elseif($payout->status === 'in_transit')
                    <div class="kt-badge kt-badge-primary kt-badge-outline">In Transit</div>
                    @elseif($payout->status === 'pending')
                    <div class="kt-badge kt-badge-warning kt-badge-outline">Pending</div>
                    @elseif($payout->status === 'failed')
                    <div class="kt-badge kt-badge-destructive kt-badge-outline">Failed</div>
                    @else
                    <div class="kt-badge kt-badge-secondary kt-badge-outline">{{ ucfirst($payout->status) }}</div>
                    @endif
                </div>
                <div class="flex items-center gap-2 text-sm text-secondary-foreground">
                    <code class="font-mono">{{ $payout->processor_payout_id ?? $payout->reference }}</code>
                    <span>•</span>
                    <span>{{ $payout->created_at->format('M d, Y \a\t H:i') }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">

            {{-- Left Column --}}
            <div class="lg:col-span-2 flex flex-col gap-5">

                {{-- Payout Summary --}}
                <div class="kt-card">
                    <div class="kt-card-content p-5 lg:p-7">
                        <h3 class="text-base font-semibold text-foreground mb-5">Payout Summary</h3>
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-between py-2">
                                <span class="text-sm text-secondary-foreground">Gross Amount</span>
                                <span class="text-sm font-medium text-foreground">{{ $amount }}</span>
                            </div>
                            <div class="flex items-center justify-between py-2 border-t border-border">
                                <span class="text-sm text-secondary-foreground">Fees</span>
                                <span class="text-sm font-medium text-foreground">-{{ $symbol }}{{ number_format(($payout->fee ?? 0) / 100, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-2 border-t-2 border-foreground/20">
                                <span class="text-sm font-semibold text-foreground">Net Amount</span>
                                <span class="text-sm font-bold text-foreground">{{ $symbol }}{{ number_format(($payout->net_amount ?? $payout->amount) / 100, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Included Transactions (Balance Transactions from Stripe) --}}
                @if($transactions->count() > 0)
                <div class="kt-card kt-card-grid">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title text-sm">Transactions in this Payout</h3>
                        <span class="text-xs text-secondary-foreground">{{ $transactions->where('type', '!=', 'payout')->count() }} transactions</span>
                    </div>
                    <div class="kt-card-content">
                        <div class="kt-scrollable-x-auto">
                            <table class="kt-table table-auto kt-table-border">
                                <thead>
                                    <tr>
                                        <th class="min-w-[180px]">Description</th>
                                        <th class="min-w-[80px]">Type</th>
                                        <th class="min-w-[100px] text-end">Gross</th>
                                        <th class="min-w-[80px] text-end">Fee</th>
                                        <th class="min-w-[100px] text-end">Net</th>
                                        <th class="min-w-[140px]">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions->where('type', '!=', 'payout') as $bt)
                                    <tr>
                                        <td>
                                            <span class="text-sm text-foreground">{{ $bt['description'] ?? '-' }}</span>
                                        </td>
                                        <td>
                                            @if($bt['type'] === 'charge')
                                            <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Charge</span>
                                            @elseif($bt['type'] === 'refund')
                                            <span class="kt-badge kt-badge-sm kt-badge-info kt-badge-outline">Refund</span>
                                            @elseif($bt['type'] === 'adjustment')
                                            <span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Adjustment</span>
                                            @else
                                            <span class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline">{{ ucfirst($bt['type']) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="text-sm font-medium text-mono">{{ $symbol }}{{ number_format($bt['amount'] / 100, 2) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-sm text-secondary-foreground">-{{ $symbol }}{{ number_format($bt['fee'] / 100, 2) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-sm font-medium text-mono">{{ $symbol }}{{ number_format($bt['net'] / 100, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="text-sm text-secondary-foreground">{{ $bt['created']->format('M d, Y H:i') }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Failure Info --}}
                @if($payout->status === 'failed' && $payout->failure_reason)
                <div class="kt-card border-destructive/30">
                    <div class="kt-card-content p-5 lg:p-7">
                        <div class="flex items-start gap-3">
                            <i class="ki-filled ki-cross-circle text-destructive text-lg mt-0.5"></i>
                            <div class="flex flex-col gap-2">
                                <h3 class="text-base font-semibold text-destructive">Payout Failed</h3>
                                <p class="text-sm text-secondary-foreground">{{ $payout->failure_reason }}</p>
                            </div>
                        </div>
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
                                    <span class="text-sm font-medium text-foreground">Initiated</span>
                                    <span class="text-xs text-secondary-foreground">{{ $payout->created_at->format('M d, Y H:i:s') }}</span>
                                </div>
                            </div>
                            @if($payout->status === 'in_transit' || $payout->status === 'paid')
                            <div class="flex items-start gap-3">
                                <div class="flex items-center justify-center size-7 rounded-full bg-primary/10 shrink-0 mt-0.5">
                                    <span class="size-2 rounded-full bg-primary"></span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-foreground">In Transit</span>
                                    <span class="text-xs text-secondary-foreground">Funds sent to bank</span>
                                </div>
                            </div>
                            @endif
                            @if($payout->status === 'paid' && $payout->arrived_at)
                            <div class="flex items-start gap-3">
                                <div class="flex items-center justify-center size-7 rounded-full shrink-0 mt-0.5" style="background-color:rgba(34,197,94,0.1)">
                                    <span class="size-2 rounded-full" style="background-color:#22c55e"></span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-foreground">Paid</span>
                                    <span class="text-xs text-secondary-foreground">{{ $payout->arrived_at->format('M d, Y H:i:s') }}</span>
                                </div>
                            </div>
                            @endif
                            @if($payout->status === 'failed')
                            <div class="flex items-start gap-3">
                                <div class="flex items-center justify-center size-7 rounded-full shrink-0 mt-0.5" style="background-color:rgba(239,68,68,0.1)">
                                    <span class="size-2 rounded-full" style="background-color:#ef4444"></span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-foreground">Failed</span>
                                    <span class="text-xs text-secondary-foreground">{{ $payout->failed_at ? $payout->failed_at->format('M d, Y H:i:s') : 'Unknown' }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Destination --}}
                <div class="kt-card">
                    <div class="kt-card-content p-5 lg:p-7">
                        <h3 class="text-base font-semibold text-foreground mb-4">Destination</h3>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10">
                                <i class="ki-filled ki-bank text-primary text-xl"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-foreground">{{ ucfirst($payout->destination_type ?? 'Bank Account') }}</span>
                                @if($payout->destination_last_four)
                                <span class="text-xs text-secondary-foreground">•••• {{ $payout->destination_last_four }}</span>
                                @endif
                            </div>
                        </div>
                        @if($payout->estimated_arrival_at)
                        <div class="flex flex-col gap-0.5 mt-3">
                            <span class="text-xs text-secondary-foreground">Expected Arrival</span>
                            <span class="text-sm text-foreground">{{ $payout->estimated_arrival_at->format('M d, Y') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Reference IDs --}}
                <div class="kt-card">
                    <div class="kt-card-content p-5 lg:p-7">
                        <h3 class="text-base font-semibold text-foreground mb-4">Reference IDs</h3>
                        <div class="flex flex-col gap-3">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Payout ID</span>
                                <code class="text-xs font-mono text-foreground break-all">{{ $payout->id }}</code>
                            </div>
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Reference</span>
                                <code class="text-xs font-mono text-foreground">{{ $payout->reference }}</code>
                            </div>
                            @if($payout->processor_payout_id)
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-secondary-foreground">Stripe Payout ID</span>
                                <code class="text-xs font-mono text-foreground break-all">{{ $payout->processor_payout_id }}</code>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
