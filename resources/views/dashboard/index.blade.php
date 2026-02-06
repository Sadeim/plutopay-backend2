@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 gap-5 lg:gap-7.5">

        <div class="flex flex-wrap items-center gap-5 justify-between">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-semibold leading-none text-mono">Dashboard</h1>
                <div class="flex items-center gap-2 text-sm text-secondary-foreground">
                    Welcome back, {{ auth()->user()->first_name }}!
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5 lg:gap-7.5">
            <div class="kt-card">
                <div class="kt-card-content p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-secondary-foreground font-medium">Total Volume</span>
                        <div class="flex items-center justify-center size-10 rounded-lg bg-primary/10">
                            <i class="ki-filled ki-dollar text-primary text-xl"></i>
                        </div>
                    </div>
                    <span class="text-2xl font-semibold text-mono">${{ number_format(($stats['total_volume'] ?? 0) / 100, 2) }}</span>
                </div>
            </div>
            <div class="kt-card">
                <div class="kt-card-content p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-secondary-foreground font-medium">Transactions</span>
                        <div class="flex items-center justify-center size-10 rounded-lg bg-success/10">
                            <i class="ki-filled ki-chart-line text-success text-xl"></i>
                        </div>
                    </div>
                    <span class="text-2xl font-semibold text-mono">{{ number_format($stats['total_transactions'] ?? 0) }}</span>
                </div>
            </div>
            <div class="kt-card">
                <div class="kt-card-content p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-secondary-foreground font-medium">Success Rate</span>
                        <div class="flex items-center justify-center size-10 rounded-lg bg-info/10">
                            <i class="ki-filled ki-check-circle text-info text-xl"></i>
                        </div>
                    </div>
                    <span class="text-2xl font-semibold text-mono">{{ number_format($stats['success_rate'] ?? 0, 1) }}%</span>
                </div>
            </div>
            <div class="kt-card">
                <div class="kt-card-content p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-secondary-foreground font-medium">Active Terminals</span>
                        <div class="flex items-center justify-center size-10 rounded-lg bg-warning/10">
                            <i class="ki-filled ki-shop text-warning text-xl"></i>
                        </div>
                    </div>
                    <span class="text-2xl font-semibold text-mono">{{ $stats['active_terminals'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <div class="kt-card">
            <div class="kt-card-header px-5">
                <h3 class="kt-card-title">Recent Transactions</h3>
                <a class="kt-btn kt-btn-sm kt-btn-light" href="{{ route('dashboard.transactions.index') }}">View All</a>
            </div>
            <div class="kt-card-content p-0">
                <div class="scrollable-x-auto">
                    <table class="kt-table kt-table-border align-middle text-sm text-secondary-foreground">
                        <thead>
                            <tr>
                                <th class="min-w-[150px] text-start px-5 py-3 font-medium text-secondary-foreground">Reference</th>
                                <th class="min-w-[120px] text-start px-5 py-3 font-medium text-secondary-foreground">Amount</th>
                                <th class="min-w-[120px] text-start px-5 py-3 font-medium text-secondary-foreground">Status</th>
                                <th class="min-w-[120px] text-start px-5 py-3 font-medium text-secondary-foreground">Method</th>
                                <th class="min-w-[150px] text-start px-5 py-3 font-medium text-secondary-foreground">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $txn)
                            <tr class="border-b border-border">
                                <td class="px-5 py-3.5 font-medium text-mono">{{ $txn->reference }}</td>
                                <td class="px-5 py-3.5 font-semibold text-mono">{{ $txn->formatted_amount }}</td>
                                <td class="px-5 py-3.5">
                                    @if($txn->status === 'succeeded')
                                        <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-success">Succeeded</span>
                                    @elseif($txn->status === 'pending')
                                        <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-warning">Pending</span>
                                    @elseif($txn->status === 'failed')
                                        <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-danger">Failed</span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-info">{{ ucfirst($txn->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">{{ ucfirst($txn->payment_method_type ?? 'N/A') }}</td>
                                <td class="px-5 py-3.5">{{ $txn->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-secondary-foreground">
                                    <div class="flex flex-col items-center gap-3">
                                        <i class="ki-filled ki-dollar text-3xl text-muted-foreground"></i>
                                        <span>No transactions yet</span>
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

    </div>
@endsection
