<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display the transactions listing page.
     * Data is loaded via KTDataTable server-side (AJAX to API).
     */
    public function index()
    {
        $merchant = auth()->user()->merchant;

        // Stats for the header cards
        $stats = [
            'total_count' => \App\Models\Transaction::where('merchant_id', $merchant->id)->count(),
            'succeeded'   => \App\Models\Transaction::where('merchant_id', $merchant->id)->where('status', 'succeeded')->count(),
            'pending'     => \App\Models\Transaction::where('merchant_id', $merchant->id)->where('status', 'pending')->count(),
            'failed'      => \App\Models\Transaction::where('merchant_id', $merchant->id)->where('status', 'failed')->count(),
        ];

        // Get the merchant's API key for server-side datatable calls
        $apiKey = \App\Models\ApiKey::where('merchant_id', $merchant->id)
            ->where('type', 'secret')
            ->whereNull('revoked_at')
            ->first();

        return view('dashboard.transactions.index', [
            'stats'  => $stats,
            'apiKey' => $apiKey?->key,
        ]);
    }
}
