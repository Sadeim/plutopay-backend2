@extends('layouts.app')
@section('title', 'Dashboard')

@php
    $currencySymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'ILS' => '₪', 'AED' => 'د.إ', 'SAR' => '﷼', 'JOD' => 'د.ا'];
    $sym = $currencySymbols[$currency] ?? $currency . ' ';

    $volChange = $stats['volume_prev_30d'] > 0
        ? round((($stats['volume_30d'] - $stats['volume_prev_30d']) / $stats['volume_prev_30d']) * 100, 1)
        : ($stats['volume_30d'] > 0 ? 100 : 0);

    $txnChange = $stats['txn_prev_30d'] > 0
        ? round((($stats['txn_30d'] - $stats['txn_prev_30d']) / $stats['txn_prev_30d']) * 100, 1)
        : ($stats['txn_30d'] > 0 ? 100 : 0);
@endphp

@section('content')
    <div class="flex flex-col gap-5 lg:gap-7.5">

        {{-- Header --}}
        <div class="flex flex-wrap items-center gap-5 justify-between">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-semibold leading-none text-mono">Dashboard</h1>
                <div class="flex items-center gap-2 text-sm text-secondary-foreground">
                    Welcome back, {{ auth()->user()->first_name }}! Here's what's happening.
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-secondary-foreground">Last 30 days</span>
            </div>
        </div>

        {{-- Main Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 lg:gap-5">
            {{-- Net Sales --}}
            <div class="kt-card">
                <div class="kt-card-content p-4 lg:p-5">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-secondary-foreground font-medium">Net Sales</span>
                            <div class="flex items-center justify-center size-8 rounded-md bg-success/10">
                                <i class="ki-filled ki-dollar text-success"></i>
                            </div>
                        </div>
                        <span class="text-xl lg:text-2xl font-semibold text-mono">{{ $sym }}{{ number_format(($stats['total_volume'] - $stats['total_tips']) / 100, 2) }}</span>
                        <div class="flex items-center gap-1">
                            @if($volChange >= 0)
                            <span class="text-xs text-success font-medium"><i class="ki-filled ki-arrow-up text-2xs"></i> {{ $volChange }}%</span>
                            @else
                            <span class="text-xs text-destructive font-medium"><i class="ki-filled ki-arrow-down text-2xs"></i> {{ abs($volChange) }}%</span>
                            @endif
                            <span class="text-xs text-secondary-foreground">vs last 30d</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tips --}}
            <div class="kt-card">
                <div class="kt-card-content p-4 lg:p-5">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-secondary-foreground font-medium">Tips</span>
                            <div class="flex items-center justify-center size-8 rounded-md bg-success/10">
                                <i class="ki-filled ki-heart text-success"></i>
                            </div>
                        </div>
                        <span class="text-xl lg:text-2xl font-semibold text-success">{{ $sym }}{{ number_format($stats['total_tips'] / 100, 2) }}</span>
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-secondary-foreground">Total: {{ $sym }}{{ number_format($stats['total_volume'] / 100, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transactions --}}
            <div class="kt-card">
                <div class="kt-card-content p-4 lg:p-5">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-secondary-foreground font-medium">Transactions</span>
                            <div class="flex items-center justify-center size-8 rounded-md bg-primary/10">
                                <i class="ki-filled ki-graph-3 text-primary"></i>
                            </div>
                        </div>
                        <span class="text-xl lg:text-2xl font-semibold text-mono">{{ number_format($stats['total_transactions']) }}</span>
                        <div class="flex items-center gap-1">
                            @if($txnChange >= 0)
                            <span class="text-xs text-success font-medium"><i class="ki-filled ki-arrow-up text-2xs"></i> {{ $txnChange }}%</span>
                            @else
                            <span class="text-xs text-destructive font-medium"><i class="ki-filled ki-arrow-down text-2xs"></i> {{ abs($txnChange) }}%</span>
                            @endif
                            <span class="text-xs text-secondary-foreground">vs last 30d</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Customers --}}
            <div class="kt-card">
                <div class="kt-card-content p-4 lg:p-5">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-secondary-foreground font-medium">Customers</span>
                            <div class="flex items-center justify-center size-8 rounded-md bg-info/10">
                                <i class="ki-filled ki-people text-info"></i>
                            </div>
                        </div>
                        <span class="text-xl lg:text-2xl font-semibold text-mono">{{ number_format($stats['total_customers']) }}</span>
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-success font-medium">+{{ $stats['customers_30d'] }}</span>
                            <span class="text-xs text-secondary-foreground">last 30d</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Success Rate --}}
            <div class="kt-card">
                <div class="kt-card-content p-4 lg:p-5">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-secondary-foreground font-medium">Success Rate</span>
                            <div class="flex items-center justify-center size-8 rounded-md bg-warning/10">
                                <i class="ki-filled ki-check-circle text-warning"></i>
                            </div>
                        </div>
                        <span class="text-xl lg:text-2xl font-semibold text-mono">{{ number_format($stats['success_rate'], 1) }}%</span>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-secondary-foreground">{{ $stats['active_terminals'] }} terminal{{ $stats['active_terminals'] !== 1 ? 's' : '' }} online</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">

            {{-- Volume Chart (2 cols) --}}
            <div class="lg:col-span-2 kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title text-sm">Revenue (Last 14 Days)</h3>
                </div>
                <div class="kt-card-content p-5">
                    <div style="position:relative;height:280px;width:100%"><canvas id="volumeChart"></canvas></div>
                </div>
            </div>

            {{-- Payment Methods Breakdown --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title text-sm">Payment Methods</h3>
                </div>
                <div class="kt-card-content p-5">
                    @if($methodBreakdown->count() > 0)
                    <div style="position:relative;height:200px;width:100%"><canvas id="methodChart"></canvas></div>
                    <div class="flex flex-col gap-2 mt-4">
                        @foreach($methodBreakdown as $method)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="size-2.5 rounded-full {{ ['card' => 'bg-primary', 'wallet' => 'bg-success', 'bank_transfer' => 'bg-info', 'terminal' => 'bg-warning'][$method->payment_method_type] ?? 'bg-secondary' }}"></span>
                                <span class="text-xs text-foreground">{{ ucfirst(str_replace('_', ' ', $method->payment_method_type ?? 'Other')) }}</span>
                            </div>
                            <span class="text-xs text-secondary-foreground">{{ $method->count }} txns</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="flex items-center justify-center h-[200px] text-sm text-secondary-foreground">
                        No data yet
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Status Breakdown + Quick Actions --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">

            {{-- Transaction Status --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title text-sm">Transaction Status</h3>
                </div>
                <div class="kt-card-content p-5">
                    <div class="flex flex-col gap-3">
                        @foreach($statusBreakdown as $status)
                        @php
                            $pct = $stats['total_transactions'] > 0 ? round(($status->count / $stats['total_transactions']) * 100) : 0;
                            $hexColors = ['succeeded' => '#22c55e', 'pending' => '#f59e0b', 'failed' => '#ef4444', 'refunded' => '#0ea5e9', 'canceled' => '#94a3b8'];
                            $barHex = $hexColors[$status->status] ?? '#94a3b8';
                        @endphp
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-foreground">{{ ucfirst($status->status) }}</span>
                                <span class="text-xs text-secondary-foreground">{{ $status->count }} ({{ $pct }}%)</span>
                            </div>
                            <div class="h-1.5 bg-muted rounded-full overflow-hidden">
                                <div class="h-full rounded-full" style="width: {{ $pct }}%; background-color: {{ $barHex }}"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Recent Payouts --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title text-sm">Recent Payouts</h3>
                    <a class="kt-btn kt-btn-sm kt-btn-light" href="{{ route('dashboard.payouts.index') }}">View All</a>
                </div>
                <div class="kt-card-content p-5">
                    @if($recentPayouts->count() > 0)
                    <div class="flex flex-col gap-3">
                        @foreach($recentPayouts as $payout)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="size-2 rounded-full {{ $payout->status === 'paid' ? 'bg-success' : ($payout->status === 'failed' ? 'bg-destructive' : 'bg-warning') }}"></div>
                                <span class="text-xs text-foreground">{{ $sym }}{{ number_format($payout->amount / 100, 2) }}</span>
                            </div>
                            <span class="text-xs text-secondary-foreground">{{ $payout->created_at->format('M d') }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="flex items-center justify-center h-[100px] text-xs text-secondary-foreground">No payouts yet</div>
                    @endif
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title text-sm">Quick Actions</h3>
                </div>
                <div class="kt-card-content p-5">
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('dashboard.transactions.index') }}" class="flex items-center gap-3 p-2.5 rounded-md hover:bg-muted transition-colors">
                            <div class="flex items-center justify-center size-8 rounded-md bg-primary/10">
                                <i class="ki-filled ki-graph-3 text-primary text-sm"></i>
                            </div>
                            <span class="text-sm text-foreground">View Transactions</span>
                        </a>
                        <a href="{{ route('dashboard.customers.index') }}" class="flex items-center gap-3 p-2.5 rounded-md hover:bg-muted transition-colors">
                            <div class="flex items-center justify-center size-8 rounded-md bg-info/10">
                                <i class="ki-filled ki-people text-info text-sm"></i>
                            </div>
                            <span class="text-sm text-foreground">Manage Customers</span>
                        </a>
                        <a href="{{ route('dashboard.terminals.index') }}" class="flex items-center gap-3 p-2.5 rounded-md hover:bg-muted transition-colors">
                            <div class="flex items-center justify-center size-8 rounded-md bg-warning/10">
                                <i class="ki-filled ki-technology-4 text-warning text-sm"></i>
                            </div>
                            <span class="text-sm text-foreground">Terminal Management</span>
                        </a>
                        <a href="{{ route('dashboard.api-keys.index') }}" class="flex items-center gap-3 p-2.5 rounded-md hover:bg-muted transition-colors">
                            <div class="flex items-center justify-center size-8 rounded-md bg-success/10">
                                <i class="ki-filled ki-key text-success text-sm"></i>
                            </div>
                            <span class="text-sm text-foreground">API Keys</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Transactions Table --}}
        <div class="kt-card kt-card-grid">
            <div class="kt-card-header">
                <h3 class="kt-card-title text-sm">Recent Transactions</h3>
                <a class="kt-btn kt-btn-sm kt-btn-light" href="{{ route('dashboard.transactions.index') }}">View All</a>
            </div>
            <div class="kt-card-content">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[140px]">Reference</th>
                                <th class="min-w-[100px]">Customer</th>
                                <th class="min-w-[100px]">Amount</th>
                                <th class="min-w-[80px]">Status</th>
                                <th class="min-w-[100px]">Method</th>
                                <th class="min-w-[130px]">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $txn)
                            <tr class="cursor-pointer hover:bg-muted/50" onclick="window.location='{{ route('dashboard.transactions.show', $txn->id) }}'">
                                <td><code class="text-xs font-mono">{{ $txn->reference }}</code></td>
                                <td class="text-sm text-secondary-foreground">{{ $txn->customer->name ?? ($txn->receipt_email ?? '-') }}</td>
                                <td class="text-sm font-medium">{{ $sym }}{{ number_format($txn->amount / 100, 2) }}</td>
                                <td>
                                    @if($txn->status === 'succeeded')
                                    <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Succeeded</span>
                                    @elseif($txn->status === 'pending')
                                    <span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Pending</span>
                                    @elseif($txn->status === 'failed')
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Failed</span>
                                    @else
                                    <span class="kt-badge kt-badge-sm kt-badge-info kt-badge-outline">{{ ucfirst($txn->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        @if($txn->card_brand)
                                        <span class="text-xs text-foreground">{{ ucfirst($txn->card_brand) }}</span>
                                        <span class="text-xs text-secondary-foreground">•••• {{ $txn->card_last_four }}</span>
                                        @else
                                        <span class="text-xs text-secondary-foreground">{{ ucfirst(str_replace('_', ' ', $txn->payment_method_type ?? '-')) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-sm text-secondary-foreground">{{ $txn->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <div class="flex flex-col items-center gap-3 py-10">
                                        <i class="ki-filled ki-dollar text-3xl text-muted-foreground"></i>
                                        <span class="text-sm text-secondary-foreground">No transactions yet</span>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Volume Chart
    const chartData = @json($chartData);
    const volumeCtx = document.getElementById('volumeChart');

    if (volumeCtx) {
        new Chart(volumeCtx, {
            type: 'bar',
            data: {
                labels: chartData.map(d => d.date),
                datasets: [{
                    label: 'Revenue ($)',
                    data: chartData.map(d => d.volume),
                    backgroundColor: 'rgba(59, 130, 246, 0.15)',
                    borderColor: 'rgba(59, 130, 246, 0.8)',
                    borderWidth: 1.5,
                    borderRadius: 4,
                    barPercentage: 0.7,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const i = ctx.dataIndex;
                                return '$' + ctx.raw.toLocaleString() + ' (' + chartData[i].count + ' txns)';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            callback: function(v) { return '$' + v.toLocaleString(); },
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    }

    // Payment Methods Doughnut
    const methodData = @json($methodBreakdown);
    const methodCtx = document.getElementById('methodChart');

    if (methodCtx && methodData.length > 0) {
        const colors = {
            'card': 'rgba(59, 130, 246, 0.8)',
            'wallet': 'rgba(34, 197, 94, 0.8)',
            'bank_transfer': 'rgba(14, 165, 233, 0.8)',
            'terminal': 'rgba(245, 158, 11, 0.8)',
        };

        new Chart(methodCtx, {
            type: 'doughnut',
            data: {
                labels: methodData.map(m => (m.payment_method_type || 'other').replace('_', ' ')),
                datasets: [{
                    data: methodData.map(m => m.count),
                    backgroundColor: methodData.map(m => colors[m.payment_method_type] || 'rgba(148,163,184,0.8)'),
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>
@endpush
