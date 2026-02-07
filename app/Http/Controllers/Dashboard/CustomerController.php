<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ApiKey;

class CustomerController extends Controller
{
    public function index()
    {
        $merchant = auth()->user()->merchant;

        $stats = [
            'total_count' => Customer::where('merchant_id', $merchant->id)->count(),
            'with_email' => Customer::where('merchant_id', $merchant->id)->whereNotNull('email')->where('email', '!=', '')->count(),
            'with_phone' => Customer::where('merchant_id', $merchant->id)->whereNotNull('phone')->where('phone', '!=', '')->count(),
            'recent_30d' => Customer::where('merchant_id', $merchant->id)->where('created_at', '>=', now()->subDays(30))->count(),
        ];

        $apiKey = ApiKey::where('merchant_id', $merchant->id)
            ->where('type', 'secret')
            ->whereNull('revoked_at')
            ->first();

        return view('dashboard.customers.index', [
            'stats' => $stats,
            'apiKey' => $apiKey?->key,
        ]);
    }
}
