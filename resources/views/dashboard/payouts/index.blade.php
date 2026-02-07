@extends('layouts.app')
@section('title', 'Payouts')

@php
    $currencySymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'ILS' => '₪', 'AED' => 'د.إ', 'SAR' => '﷼', 'JOD' => 'د.ا'];
    $symbol = $currencySymbols[$currency] ?? $currency . ' ';
@endphp

@section('content')
    <div class="flex flex-col gap-5 lg:gap-7.5">

        {{-- Page Header --}}
        <div class="flex flex-wrap items-center gap-5 justify-between">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-semibold leading-none text-mono">Payouts</h1>
                <div class="flex items-center gap-2 text-sm text-secondary-foreground">
                    Track your payouts to your bank account
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="flex items-center flex-wrap gap-2 lg:gap-5">
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-mono text-2xl leading-none font-semibold">{{ $stats['total_count'] }}</span>
                <span class="text-secondary-foreground text-sm">Total Payouts</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-success text-2xl leading-none font-semibold">{{ $symbol }}{{ number_format($stats['total_amount'] / 100, 2) }}</span>
                <span class="text-secondary-foreground text-sm">Total Paid</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-warning text-2xl leading-none font-semibold">{{ $stats['pending'] }}</span>
                <span class="text-secondary-foreground text-sm">Pending</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-primary text-2xl leading-none font-semibold">{{ $stats['in_transit'] }}</span>
                <span class="text-secondary-foreground text-sm">In Transit</span>
            </div>
        </div>

        {{-- Payouts Table --}}
        <div class="kt-card kt-card-grid">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">All Payouts</h3>
            </div>
            <div class="kt-card-content">
                @if($payouts->count() > 0)
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[120px]">Reference</th>
                                <th class="min-w-[100px]">Amount</th>
                                <th class="min-w-[80px]">Fee</th>
                                <th class="min-w-[100px]">Net</th>
                                <th class="min-w-[100px]">Status</th>
                                <th class="min-w-[120px]">Destination</th>
                                <th class="min-w-[140px]">Arrival</th>
                                <th class="min-w-[140px]">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payouts as $payout)
                            <tr>
                                <td><code class="text-xs font-mono">{{ $payout->reference }}</code></td>
                                <td class="text-sm font-medium">{{ $symbol }}{{ number_format($payout->amount / 100, 2) }}</td>
                                <td class="text-sm text-secondary-foreground">{{ $symbol }}{{ number_format(($payout->fee ?? 0) / 100, 2) }}</td>
                                <td class="text-sm font-medium">{{ $symbol }}{{ number_format(($payout->net_amount ?? $payout->amount) / 100, 2) }}</td>
                                <td>
                                    @if($payout->status === 'paid')
                                    <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Paid</span>
                                    @elseif($payout->status === 'in_transit')
                                    <span class="kt-badge kt-badge-sm kt-badge-primary kt-badge-outline">In Transit</span>
                                    @elseif($payout->status === 'pending')
                                    <span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Pending</span>
                                    @elseif($payout->status === 'failed')
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Failed</span>
                                    @else
                                    <span class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline">{{ ucfirst($payout->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-sm text-secondary-foreground">
                                    @if($payout->destination_last_four)
                                    •••• {{ $payout->destination_last_four }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="text-sm text-secondary-foreground">
                                    {{ $payout->estimated_arrival_at ? $payout->estimated_arrival_at->format('M d, Y') : '-' }}
                                </td>
                                <td class="text-sm text-secondary-foreground">{{ $payout->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($payouts->hasPages())
                <div class="kt-card-footer justify-center">
                    {{ $payouts->links() }}
                </div>
                @endif

                @else
                <div class="flex flex-col items-center gap-3 p-10">
                    <i class="ki-filled ki-bank text-3xl text-muted-foreground"></i>
                    <span class="text-sm text-secondary-foreground">No payouts yet</span>
                    <span class="text-xs text-muted-foreground">Payouts are automatically created when your balance reaches the threshold</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Documentation --}}
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-information-2 text-info text-lg mt-0.5"></i>
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-medium text-foreground">Payout Schedule</span>
                        <p class="text-xs text-secondary-foreground leading-relaxed">
                            Payouts are processed automatically on a rolling basis. Funds from completed payments are typically available for payout within 2 business days.
                            The payout schedule and minimum amounts can be configured by contacting support.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
