@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <div class="flex flex-col gap-5 lg:gap-7.5">

        <div class="flex flex-wrap items-center gap-5 justify-between">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-semibold leading-none text-mono">Dashboard</h1>
                <div class="flex items-center gap-2 text-sm text-secondary-foreground">
                    Welcome back, {{ auth()->user()->first_name }}!
                </div>
            </div>
        </div>

        {{-- Stats Cards - Using flex for horizontal layout --}}
        <div class="flex items-center flex-wrap gap-2 lg:gap-5">
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-mono text-2xl leading-none font-semibold">${{ number_format(($stats['total_volume'] ?? 0) / 100, 2) }}</span>
                <span class="text-secondary-foreground text-sm">Total Volume</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-mono text-2xl leading-none font-semibold">{{ number_format($stats['total_transactions'] ?? 0) }}</span>
                <span class="text-secondary-foreground text-sm">Transactions</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-mono text-2xl leading-none font-semibold">{{ number_format($stats['success_rate'] ?? 0, 1) }}%</span>
                <span class="text-secondary-foreground text-sm">Success Rate</span>
            </div>
            <div class="flex flex-col content-between gap-1.5 border border-dashed border-input shrink-0 rounded-md px-3.5 py-2 min-w-24 grow">
                <span class="text-mono text-2xl leading-none font-semibold">{{ $stats['active_terminals'] ?? 0 }}</span>
                <span class="text-secondary-foreground text-sm">Active Terminals</span>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Recent Transactions</h3>
                <a class="kt-btn kt-btn-sm kt-btn-light" href="{{ route('dashboard.transactions.index') }}">View All</a>
            </div>
            <div class="kt-card-table kt-scrollable-x-auto">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th class="min-w-52">Reference</th>
                            <th class="min-w-24 text-end">Amount</th>
                            <th class="min-w-24 text-end">Status</th>
                            <th class="min-w-32 text-end">Method</th>
                            <th class="min-w-32 text-end">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $txn)
                        <tr>
                            <td class="text-sm text-foreground font-normal">{{ $txn->reference }}</td>
                            <td class="text-sm text-foreground font-normal lg:text-end">{{ $txn->formatted_amount }}</td>
                            <td class="lg:text-end">
                                @if($txn->status === 'succeeded')
                                    <div class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Succeeded</div>
                                @elseif($txn->status === 'pending')
                                    <div class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Pending</div>
                                @elseif($txn->status === 'failed')
                                    <div class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Failed</div>
                                @else
                                    <div class="kt-badge kt-badge-sm kt-badge-info kt-badge-outline">{{ ucfirst($txn->status) }}</div>
                                @endif
                            </td>
                            <td class="text-sm text-secondary-foreground font-normal lg:text-end">{{ ucfirst($txn->payment_method_type ?? 'N/A') }}</td>
                            <td class="text-sm text-secondary-foreground font-normal lg:text-end">{{ $txn->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <div class="flex flex-col items-center gap-3 py-10">
                                    <i class="ki-filled ki-dollar text-3xl text-muted-foreground"></i>
                                    <span class="text-sm text-secondary-foreground">No transactions yet</span>
                                    <span class="text-xs text-muted-foreground">Transactions will appear here once you start processing payments</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
