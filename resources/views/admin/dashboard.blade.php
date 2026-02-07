@extends('admin.layout')
@section('title', 'Dashboard')

@section('content')
<!-- Stats -->
<div class="grid grid-cols-4 gap-4 mb-8">
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Total Merchants</div>
        <div class="text-2xl font-bold">{{ $stats['total_merchants'] }}</div>
        <div class="text-xs text-green-400 mt-1">{{ $stats['active_merchants'] }} active</div>
    </div>
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Total Volume</div>
        <div class="text-2xl font-bold">${{ number_format($stats['total_volume'] / 100, 2) }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ $stats['total_transactions'] }} transactions</div>
    </div>
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Today</div>
        <div class="text-2xl font-bold">${{ number_format($stats['volume_today'] / 100, 2) }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ $stats['transactions_today'] }} transactions</div>
    </div>
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
        <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Terminals</div>
        <div class="text-2xl font-bold">{{ $stats['total_terminals'] }}</div>
        <div class="text-xs text-green-400 mt-1">{{ $stats['online_terminals'] }} online</div>
    </div>
</div>

<div class="grid grid-cols-2 gap-6">
    <!-- Recent Transactions -->
    <div class="bg-gray-900 rounded-xl border border-gray-800">
        <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
            <h3 class="font-semibold text-sm">Recent Transactions</h3>
            <a href="{{ route('admin.transactions.index') }}" class="text-xs text-red-400 hover:text-red-300">View all →</a>
        </div>
        <div class="divide-y divide-gray-800">
            @forelse($recentTransactions as $txn)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium">${{ number_format($txn->amount / 100, 2) }}</div>
                        <div class="text-xs text-gray-500">{{ $txn->merchant->business_name ?? 'N/A' }}</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-medium
                            {{ $txn->status === 'succeeded' ? 'bg-green-500/10 text-green-400' : '' }}
                            {{ $txn->status === 'pending' ? 'bg-yellow-500/10 text-yellow-400' : '' }}
                            {{ $txn->status === 'failed' ? 'bg-red-500/10 text-red-400' : '' }}
                            {{ $txn->status === 'canceled' ? 'bg-gray-500/10 text-gray-400' : '' }}">
                            {{ $txn->status }}
                        </span>
                        <div class="text-xs text-gray-500 mt-0.5">{{ $txn->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-gray-500 text-sm">No transactions yet</div>
            @endforelse
        </div>
    </div>

    <!-- Recent Merchants -->
    <div class="bg-gray-900 rounded-xl border border-gray-800">
        <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
            <h3 class="font-semibold text-sm">Recent Merchants</h3>
            <a href="{{ route('admin.merchants.index') }}" class="text-xs text-red-400 hover:text-red-300">View all →</a>
        </div>
        <div class="divide-y divide-gray-800">
            @forelse($recentMerchants as $merchant)
                <a href="{{ route('admin.merchants.show', $merchant->id) }}" class="px-5 py-3 flex items-center justify-between hover:bg-gray-800/50 block">
                    <div>
                        <div class="text-sm font-medium">{{ $merchant->business_name }}</div>
                        <div class="text-xs text-gray-500">{{ $merchant->email }}</div>
                    </div>
                    <div>
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-medium
                            {{ $merchant->status === 'active' ? 'bg-green-500/10 text-green-400' : 'bg-gray-500/10 text-gray-400' }}">
                            {{ $merchant->status }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="px-5 py-8 text-center text-gray-500 text-sm">No merchants yet</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
