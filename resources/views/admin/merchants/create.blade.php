@extends('admin.layout')
@section('title', 'New Merchant')

@section('content')
<a href="{{ route('admin.merchants.index') }}" class="text-sm text-gray-400 hover:text-white mb-4 inline-block">← Back to Merchants</a>

<form method="POST" action="{{ route('admin.merchants.store') }}" class="max-w-3xl">
    @csrf

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
                <input type="text" name="business_name" value="{{ old('business_name') }}" required
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Display Name</label>
                <input type="text" name="display_name" value="{{ old('display_name') }}"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Website</label>
                <input type="url" name="website" value="{{ old('website') }}" placeholder="https://"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Currency *</label>
                <select name="default_currency" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white">
                    <option value="USD" {{ old('default_currency') === 'USD' ? 'selected' : '' }}>USD</option>
                    <option value="EUR" {{ old('default_currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                    <option value="GBP" {{ old('default_currency') === 'GBP' ? 'selected' : '' }}>GBP</option>
                    <option value="AED" {{ old('default_currency') === 'AED' ? 'selected' : '' }}>AED</option>
                    <option value="SAR" {{ old('default_currency') === 'SAR' ? 'selected' : '' }}>SAR</option>
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
                <input type="text" name="address_line1" value="{{ old('address_line1') }}"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">City</label>
                <input type="text" name="city" value="{{ old('city') }}"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">State</label>
                <input type="text" name="state" value="{{ old('state') }}"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Postal Code</label>
                <input type="text" name="postal_code" value="{{ old('postal_code') }}"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Country</label>
                <select name="country" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white">
                    <option value="US" {{ old('country', 'US') === 'US' ? 'selected' : '' }}>United States</option>
                    <option value="CA" {{ old('country') === 'CA' ? 'selected' : '' }}>Canada</option>
                    <option value="GB" {{ old('country') === 'GB' ? 'selected' : '' }}>United Kingdom</option>
                    <option value="AE" {{ old('country') === 'AE' ? 'selected' : '' }}>UAE</option>
                    <option value="SA" {{ old('country') === 'SA' ? 'selected' : '' }}>Saudi Arabia</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Payment Settings -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <h3 class="font-semibold mb-4">Payment Settings</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-400 mb-1">Stripe Connected Account</label>
                <input type="text" name="processor_account_id" value="{{ old('processor_account_id') }}" placeholder="acct_xxxxxxxxxx"
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white font-mono focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Mode</label>
                <select name="test_mode" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white">
                    <option value="0" {{ old('test_mode') === '0' ? 'selected' : '' }}>Live</option>
                    <option value="1" {{ old('test_mode', '1') === '1' ? 'selected' : '' }}>Test</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Owner Account -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <h3 class="font-semibold mb-4">Owner Account (Login Credentials)</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-400 mb-1">First Name *</label>
                <input type="text" name="user_first_name" value="{{ old('user_first_name') }}" required
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Last Name *</label>
                <input type="text" name="user_last_name" value="{{ old('user_last_name') }}" required
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Email *</label>
                <input type="email" name="user_email" value="{{ old('user_email') }}" required
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Password *</label>
                <input type="text" name="user_password" value="{{ old('user_password') }}" required
                    class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-red-500">
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition">
            Create Merchant
        </button>
        <a href="{{ route('admin.merchants.index') }}" class="px-6 py-2.5 bg-gray-800 hover:bg-gray-700 text-white text-sm rounded-lg transition">Cancel</a>
    </div>
</form>
@endsection
