@extends('admin.layout')
@section('title', $merchant->business_name)

@section('content')
<a href="{{ route('admin.merchants.index') }}" class="text-sm text-gray-400 hover:text-white mb-4 inline-block">← Back to Merchants</a>

<!-- Merchant Info -->
<div class="grid grid-cols-3 gap-6 mb-8">
    <div class="col-span-2 bg-gray-900 rounded-xl border border-gray-800 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold">Business Details</h3>
            <a href="{{ route('admin.merchants.edit', $merchant->id) }}" class="text-xs text-red-400 hover:text-red-300">Edit →</a>
            <span class="px-2 py-0.5 rounded text-xs font-medium
                {{ $merchant->status === 'active' ? 'bg-green-500/10 text-green-400' : '' }}
                {{ $merchant->status === 'suspended' ? 'bg-red-500/10 text-red-400' : '' }}">
                {{ $merchant->status }}
            </span>
        </div>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Business Name:</span> <span class="ml-2">{{ $merchant->business_name }}</span></div>
            <div><span class="text-gray-500">Email:</span> <span class="ml-2">{{ $merchant->email }}</span></div>
            <div><span class="text-gray-500">Phone:</span> <span class="ml-2">{{ $merchant->phone ?? 'N/A' }}</span></div>
            <div><span class="text-gray-500">Currency:</span> <span class="ml-2">{{ strtoupper($merchant->default_currency) }}</span></div>
            <div><span class="text-gray-500">Mode:</span> <span class="ml-2 {{ $merchant->test_mode ? 'text-yellow-400' : 'text-green-400' }}">{{ $merchant->test_mode ? 'Test' : 'Live' }}</span></div>
            <div><span class="text-gray-500">Processor Account:</span> <span class="ml-2 font-mono text-xs">{{ $merchant->processor_account_id ?? 'N/A' }}</span></div>
            <div><span class="text-gray-500">Created:</span> <span class="ml-2">{{ $merchant->created_at->format('M d, Y H:i') }}</span></div>
            <div><span class="text-gray-500">ID:</span> <span class="ml-2 font-mono text-xs text-gray-400">{{ $merchant->id }}</span></div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <h3 class="font-semibold mb-4">Quick Actions</h3>
        <form method="POST" action="{{ route('admin.merchants.update', $merchant->id) }}" class="space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="text-xs text-gray-500">Status</label>
                <select name="status" class="w-full mt-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white">
                    <option value="active" {{ $merchant->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ $merchant->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="inactive" {{ $merchant->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500">Mode</label>
                <select name="test_mode" class="w-full mt-1 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white">
                    <option value="0" {{ !$merchant->test_mode ? 'selected' : '' }}>Live</option>
                    <option value="1" {{ $merchant->test_mode ? 'selected' : '' }}>Test</option>
                </select>
            </div>
            <button class="w-full py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg transition mt-2">Update</button>
        </form>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-5 gap-4 mb-8">
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 text-center">
        <div class="text-xl font-bold">${{ number_format($stats['total_volume'] / 100, 2) }}</div>
        <div class="text-xs text-gray-500 mt-1">Total Volume</div>
    </div>
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 text-center">
        <div class="text-xl font-bold">{{ $stats['total_transactions'] }}</div>
        <div class="text-xs text-gray-500 mt-1">Transactions</div>
    </div>
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 text-center">
        <div class="text-xl font-bold text-green-400">{{ $stats['successful'] }}</div>
        <div class="text-xs text-gray-500 mt-1">Successful</div>
    </div>
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 text-center">
        <div class="text-xl font-bold">{{ $stats['terminals'] }}</div>
        <div class="text-xs text-gray-500 mt-1">Terminals</div>
    </div>
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-4 text-center">
        <div class="text-xl font-bold">{{ $stats['users'] }}</div>
        <div class="text-xs text-gray-500 mt-1">Users</div>
    </div>
</div>

<!-- Users -->
<div class="bg-gray-900 rounded-xl border border-gray-800 mb-6">
    <div class="px-5 py-4 border-b border-gray-800"><h3 class="font-semibold text-sm">Users</h3></div>
    <table class="w-full">
        <thead><tr class="border-b border-gray-800 text-xs text-gray-500 uppercase"><th class="px-5 py-2 text-left">Name</th><th class="px-5 py-2 text-left">Email</th><th class="px-5 py-2 text-left">Role</th></tr></thead>
        <tbody class="divide-y divide-gray-800">
            @foreach($users as $user)
                <tr><td class="px-5 py-2 text-sm">{{ $user->first_name }} {{ $user->last_name }}</td><td class="px-5 py-2 text-sm text-gray-400">{{ $user->email }}</td><td class="px-5 py-2 text-sm text-gray-400">{{ $user->role }}</td></tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Terminals -->
<div class="bg-gray-900 rounded-xl border border-gray-800 mb-6">
    <div class="px-5 py-4 border-b border-gray-800"><h3 class="font-semibold text-sm">Terminals</h3></div>
    <table class="w-full">
        <thead><tr class="border-b border-gray-800 text-xs text-gray-500 uppercase"><th class="px-5 py-2 text-left">Name</th><th class="px-5 py-2 text-left">Model</th><th class="px-5 py-2 text-left">Status</th><th class="px-5 py-2 text-left">Location</th></tr></thead>
        <tbody class="divide-y divide-gray-800">
            @foreach($terminals as $t)
                <tr>
                    <td class="px-5 py-2 text-sm">{{ $t->name }}</td>
                    <td class="px-5 py-2 text-sm text-gray-400">{{ $t->model }}</td>
                    <td class="px-5 py-2"><span class="text-xs {{ $t->status === 'online' ? 'text-green-400' : 'text-gray-400' }}">{{ $t->status }}</span></td>
                    <td class="px-5 py-2 text-sm text-gray-400">{{ $t->location_name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Recent Transactions -->
<div class="bg-gray-900 rounded-xl border border-gray-800">
    <div class="px-5 py-4 border-b border-gray-800"><h3 class="font-semibold text-sm">Recent Transactions</h3></div>
    <table class="w-full">
        <thead><tr class="border-b border-gray-800 text-xs text-gray-500 uppercase"><th class="px-5 py-2 text-left">Amount</th><th class="px-5 py-2 text-left">Status</th><th class="px-5 py-2 text-left">Type</th><th class="px-5 py-2 text-left">Date</th></tr></thead>
        <tbody class="divide-y divide-gray-800">
            @forelse($transactions as $txn)
                <tr>
                    <td class="px-5 py-2 text-sm font-medium">${{ number_format($txn->amount / 100, 2) }}</td>
                    <td class="px-5 py-2"><span class="text-xs px-2 py-0.5 rounded {{ $txn->status === 'succeeded' ? 'bg-green-500/10 text-green-400' : 'bg-gray-500/10 text-gray-400' }}">{{ $txn->status }}</span></td>
                    <td class="px-5 py-2 text-sm text-gray-400">{{ $txn->payment_method ?? 'N/A' }}</td>
                    <td class="px-5 py-2 text-sm text-gray-500">{{ $txn->created_at->format('M d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-5 py-6 text-center text-gray-500 text-sm">No transactions</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
