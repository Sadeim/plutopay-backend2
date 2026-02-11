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
                <span class="text-mono text-2xl leading-none font-semibold">{{ $stats['paid_count'] }}</span>
                <span class="text-secondary-foreground text-sm">Completed</span>
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
                            <tr class="cursor-pointer hover:bg-muted/50" onclick="window.location='{{ route('dashboard.payouts.show', $payout->id) }}'">
                                <th class="min-w-[180px]">Payout</th>
                                <th class="min-w-[100px] text-end">Amount</th>
                                <th class="min-w-[100px]">Status</th>
                                <th class="min-w-[140px]">Bank Account</th>
                                <th class="min-w-[140px]">Expected Arrival</th>
                                <th class="min-w-[140px]">Initiated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payouts as $payout)
                            <tr class="cursor-pointer hover:bg-muted/50" onclick="window.location='{{ route('dashboard.payouts.show', $payout->id) }}'">
                                <td>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-sm font-medium text-mono">{{ $symbol }}{{ number_format($payout->amount / 100, 2) }}</span>
                                        <code class="text-2xs text-muted-foreground">{{ $payout->processor_payout_id ?? $payout->reference }}</code>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="text-sm font-semibold text-mono">{{ $symbol }}{{ number_format($payout->amount / 100, 2) }}</span>
                                </td>
                                <td>
                                    @if($payout->status === 'paid')
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-success"></span>
                                        <span class="text-sm text-success font-medium">Paid</span>
                                    </div>
                                    @elseif($payout->status === 'in_transit')
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-primary"></span>
                                        <span class="text-sm text-primary font-medium">In Transit</span>
                                    </div>
                                    @elseif($payout->status === 'pending')
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-warning"></span>
                                        <span class="text-sm text-warning font-medium">Pending</span>
                                    </div>
                                    @elseif($payout->status === 'failed')
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-destructive"></span>
                                        <span class="text-sm text-destructive font-medium">Failed</span>
                                    </div>
                                    @if($payout->failure_reason)
                                    <span class="text-2xs text-destructive">{{ $payout->failure_reason }}</span>
                                    @endif
                                    @else
                                    <span class="text-sm text-secondary-foreground">{{ ucfirst($payout->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <i class="ki-filled ki-bank text-base text-muted-foreground"></i>
                                        <div class="flex flex-col">
                                            <span class="text-sm text-foreground">{{ ucfirst($payout->destination_type ?? 'Bank') }}</span>
                                            @if($payout->destination_last_four)
                                            <span class="text-2xs text-muted-foreground">•••• {{ $payout->destination_last_four }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($payout->status === 'paid' && $payout->arrived_at)
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-sm text-foreground">{{ $payout->arrived_at->format('M d, Y') }}</span>
                                        <span class="text-2xs text-success">Arrived</span>
                                    </div>
                                    @elseif($payout->estimated_arrival_at)
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-sm text-foreground">{{ $payout->estimated_arrival_at->format('M d, Y') }}</span>
                                        <span class="text-2xs text-muted-foreground">Estimated</span>
                                    </div>
                                    @else
                                    <span class="text-sm text-muted-foreground">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-sm text-foreground">{{ $payout->created_at->format('M d, Y') }}</span>
                                        <span class="text-2xs text-muted-foreground">{{ $payout->created_at->format('h:i A') }}</span>
                                    </div>
                                </td>
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

        {{-- Payout Info --}}
        <div class="kt-card">
            <div class="kt-card-content p-5">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-information-2 text-info text-lg mt-0.5"></i>
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-medium text-foreground">Payout Schedule</span>
                        <p class="text-xs text-secondary-foreground leading-relaxed">
                            Payouts are processed automatically on a rolling basis. Funds from completed payments are typically available for payout within 2 business days.
                            Payouts are synced automatically via webhooks — new payouts will appear here in real-time.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
