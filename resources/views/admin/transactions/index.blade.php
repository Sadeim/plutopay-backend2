@extends('admin.layout')
@section('title', 'Transactions')

@section('content')
<div class="grid grid-cols-5 gap-4 mb-6">
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 text-center">
        <div class="text-xl font-bold">{{ $stats['total'] }}</div>
        <div class="text-xs text-gray-500 mt-1">Total</div>
    </div>
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 text-center">
        <div class="text-xl font-bold text-green-400">{{ $stats['succeeded'] }}</div>
        <div class="text-xs text-gray-500 mt-1">Succeeded</div>
    </div>
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 text-center">
        <div class="text-xl font-bold text-yellow-400">{{ $stats['pending'] }}</div>
        <div class="text-xs text-gray-500 mt-1">Pending</div>
    </div>
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 text-center">
        <div class="text-xl font-bold text-red-400">{{ $stats['failed'] }}</div>
        <div class="text-xs text-gray-500 mt-1">Failed</div>
    </div>
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 text-center">
        <div class="text-xl font-bold">${{ number_format($stats['total_volume'] / 100, 2) }}</div>
        <div class="text-xs text-gray-500 mt-1">Volume</div>
    </div>
</div>

<div class="flex items-center gap-3 mb-6">
    <form method="GET" class="flex items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by reference or processor ID..."
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white placeholder-gray-500 focus:outline-none focus:border-red-500 w-80">
        <select name="status" onchange="this.form.submit()"
            class="px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white">
            <option value="">All Status</option>
            <option value="succeeded" {{ request('status') === 'succeeded' ? 'selected' : '' }}>Succeeded</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
            <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Canceled</option>
        </select>
        <button class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">Search</button>
    </form>
</div>

<div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-800 text-xs text-gray-500 uppercase tracking-wide">
                <th class="px-5 py-3 text-left">Amount</th>
                <th class="px-5 py-3 text-left">Merchant</th>
                <th class="px-5 py-3 text-left">Status</th>
                <th class="px-5 py-3 text-left">Type</th>
                <th class="px-5 py-3 text-left">Reference</th>
                <th class="px-5 py-3 text-left">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            @forelse($transactions as $txn)
                <tr class="hover:bg-gray-800/50">
                    <td class="px-5 py-3 text-sm font-medium">${{ number_format($txn->amount / 100, 2) }}</td>
                    <td class="px-5 py-3 text-sm text-gray-400">{{ $txn->merchant->business_name ?? 'N/A' }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-medium
                            {{ $txn->status === 'succeeded' ? 'bg-green-500/10 text-green-400' : '' }}
                            {{ $txn->status === 'pending' ? 'bg-yellow-500/10 text-yellow-400' : '' }}
                            {{ $txn->status === 'failed' ? 'bg-red-500/10 text-red-400' : '' }}
                            {{ $txn->status === 'canceled' ? 'bg-gray-500/10 text-gray-400' : '' }}">
                            {{ $txn->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-400">{{ $txn->payment_method ?? 'N/A' }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500 font-mono text-xs">{{ Str::limit($txn->reference, 20) }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500">{{ $txn->created_at->format('M d, H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500 text-sm">No transactions found</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $transactions->links() }}</div>
@endsection
