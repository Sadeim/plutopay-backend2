@extends('admin.layout')
@section('title', 'Settings')

@section('content')
<div class="max-w-2xl">
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <h3 class="font-semibold mb-4">Platform Overview</h3>
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center p-4 bg-gray-800 rounded-lg">
                <div class="text-2xl font-bold">{{ $platformStats['total_merchants'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Total Merchants</div>
            </div>
            <div class="text-center p-4 bg-gray-800 rounded-lg">
                <div class="text-2xl font-bold text-green-400">{{ $platformStats['live_merchants'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Live Mode</div>
            </div>
            <div class="text-center p-4 bg-gray-800 rounded-lg">
                <div class="text-2xl font-bold text-yellow-400">{{ $platformStats['test_merchants'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Test Mode</div>
            </div>
        </div>
    </div>

    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <h3 class="font-semibold mb-4">Payment Processor</h3>
        <div class="text-sm text-gray-400 space-y-2">
            <div class="flex justify-between"><span>Provider:</span> <span class="text-white">Stripe</span></div>
            <div class="flex justify-between"><span>Webhook (Direct):</span> <span class="text-green-400 text-xs">Active</span></div>
            <div class="flex justify-between"><span>Webhook (Connect):</span> <span class="text-green-400 text-xs">Active</span></div>
        </div>
    </div>

    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <h3 class="font-semibold mb-4">Platform Fees</h3>
        <div class="text-sm text-gray-400 space-y-2">
            <div class="flex justify-between"><span>Transaction Fee:</span> <span class="text-white">3%</span></div>
            <div class="flex justify-between"><span>Fee Type:</span> <span class="text-white">Destination Charge</span></div>
        </div>
    </div>
</div>
@endsection
