<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Payout;

class PayoutController extends Controller
{
    public function index()
    {
        $merchant = auth()->user()->merchant;

        $payouts = Payout::where('merchant_id', $merchant->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'total_count' => Payout::where('merchant_id', $merchant->id)->count(),
            'total_amount' => Payout::where('merchant_id', $merchant->id)->where('status', 'paid')->sum('amount'),
            'total_fees' => Payout::where('merchant_id', $merchant->id)->sum('fee'),
            'pending' => Payout::where('merchant_id', $merchant->id)->where('status', 'pending')->count(),
            'in_transit' => Payout::where('merchant_id', $merchant->id)->where('status', 'in_transit')->count(),
            'paid_count' => Payout::where('merchant_id', $merchant->id)->where('status', 'paid')->count(),
        ];

        return view('dashboard.payouts.index', [
            'payouts' => $payouts,
            'stats' => $stats,
            'currency' => $merchant->default_currency ?? 'USD',
        ]);
    }
}
