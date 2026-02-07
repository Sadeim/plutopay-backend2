@extends('admin.layout')
@section('title', 'Merchants')

@section('content')
<div class="flex items-center justify-between mb-6">
    <form method="GET" class="flex items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search merchants..."
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white placeholder-gray-500 focus:outline-none focus:border-red-500 w-72">
        <select name="status" onchange="this.form.submit()"
            class="px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none">
            <option value="">All Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">Search</button>
    </form>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.merchants.create') }}" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">+ New Merchant</a>
        <span class="text-sm text-gray-500">{{ $merchants->total() }} merchants</span>
    </div>
</div>

<div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-800 text-xs text-gray-500 uppercase tracking-wide">
                <th class="px-5 py-3 text-left">Business</th>
                <th class="px-5 py-3 text-left">Email</th>
                <th class="px-5 py-3 text-left">Status</th>
                <th class="px-5 py-3 text-left">Mode</th>
                <th class="px-5 py-3 text-left">Transactions</th>
                <th class="px-5 py-3 text-left">Created</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            @forelse($merchants as $merchant)
                <tr class="hover:bg-gray-800/50">
                    <td class="px-5 py-3 text-sm font-medium">{{ $merchant->business_name }}</td>
                    <td class="px-5 py-3 text-sm text-gray-400">{{ $merchant->email }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-medium
                            {{ $merchant->status === 'active' ? 'bg-green-500/10 text-green-400' : '' }}
                            {{ $merchant->status === 'suspended' ? 'bg-red-500/10 text-red-400' : '' }}
                            {{ $merchant->status === 'inactive' ? 'bg-gray-500/10 text-gray-400' : '' }}">
                            {{ $merchant->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs {{ $merchant->test_mode ? 'text-yellow-400' : 'text-green-400' }}">
                            {{ $merchant->test_mode ? 'Test' : 'Live' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-400">{{ $merchant->transactions_count }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500">{{ $merchant->created_at->format('M d, Y') }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.merchants.show', $merchant->id) }}" class="text-red-400 hover:text-red-300 text-sm">View →</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-8 text-center text-gray-500 text-sm">No merchants found</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $merchants->links() }}</div>
@endsection
