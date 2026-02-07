@extends('admin.layout')
@section('title', 'Edit: ' . $merchant->business_name)

@section('content')
<a href="{{ route('admin.merchants.show', $merchant->id) }}" class="text-sm text-gray-400 hover:text-white mb-4 inline-block">← Back to Merchant</a>

<form method="POST" action="{{ route('admin.merchants.update', $merchant->id) }}" class="max-w-3xl">
    @csrf @method('PUT')

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <!-- Business Info -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <h3 class="font-semibold mb-4">Business Information</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-400 mb-1">Business Name *</label>
                <input type="text" name="business_name" value="{{ old('business_name', $merchant->business_name) }}" required
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Display Name</label>
                <input type="text" name="display_name" value="{{ old('display_name', $merchant->display_name) }}"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Email *</label>
                <input type="email" name="email" value="{{ old('email', $merchant->email) }}" required
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $merchant->phone) }}"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Website</label>
                <input type="url" name="website" value="{{ old('website', $merchant->website) }}" placeholder="https://"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Currency *</label>
                <select name="default_currency" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white">
                    @foreach(['USD','EUR','GBP','AED','SAR'] as $cur)
                        <option value="{{ $cur }}" {{ old('default_currency', $merchant->default_currency) === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Address -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <h3 class="font-semibold mb-4">Address</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-xs text-gray-400 mb-1">Address</label>
                <input type="text" name="address_line1" value="{{ old('address_line1', $merchant->address_line1) }}"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">City</label>
                <input type="text" name="city" value="{{ old('city', $merchant->city) }}"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">State</label>
                <input type="text" name="state" value="{{ old('state', $merchant->state) }}"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Postal Code</label>
                <input type="text" name="postal_code" value="{{ old('postal_code', $merchant->postal_code) }}"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Country</label>
                <select name="country" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white">
                    @foreach(['US' => 'United States', 'CA' => 'Canada', 'GB' => 'United Kingdom', 'AE' => 'UAE', 'SA' => 'Saudi Arabia'] as $code => $name)
                        <option value="{{ $code }}" {{ old('country', $merchant->country) === $code ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Payment & Status -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <h3 class="font-semibold mb-4">Payment & Status</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs text-gray-400 mb-1">Stripe Connected Account</label>
                <input type="text" name="processor_account_id" value="{{ old('processor_account_id', $merchant->processor_account_id) }}" placeholder="acct_xxxxxxxxxx"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white font-mono focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Mode</label>
                <select name="test_mode" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white">
                    <option value="0" {{ !$merchant->test_mode ? 'selected' : '' }}>Live</option>
                    <option value="1" {{ $merchant->test_mode ? 'selected' : '' }}>Test</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white">
                    <option value="active" {{ $merchant->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ $merchant->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="inactive" {{ $merchant->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Users (read only) -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <h3 class="font-semibold mb-4">Users</h3>
        <table class="w-full">
            <thead><tr class="text-xs text-gray-500 uppercase border-b border-gray-800"><th class="py-2 text-left">Name</th><th class="py-2 text-left">Email</th><th class="py-2 text-left">Role</th></tr></thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($users as $user)
                    <tr><td class="py-2 text-sm">{{ $user->first_name }} {{ $user->last_name }}</td><td class="py-2 text-sm text-gray-400">{{ $user->email }}</td><td class="py-2 text-sm text-gray-400">{{ $user->role }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition">
            Save Changes
        </button>
        <a href="{{ route('admin.merchants.show', $merchant->id) }}" class="px-6 py-2.5 bg-gray-800 hover:bg-gray-700 text-white text-sm rounded-lg transition">Cancel</a>
    </div>
</form>
@endsection
