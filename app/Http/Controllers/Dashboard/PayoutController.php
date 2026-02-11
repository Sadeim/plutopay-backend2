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
            ->orderByDesc('estimated_arrival_at')
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

    public function show(string $id)
    {
        $merchant = auth()->user()->merchant;
        $payout = Payout::where('merchant_id', $merchant->id)->findOrFail($id);

        // Try to get balance transaction details from Stripe
        $balanceTransaction = null;
        $transactions = collect();

        try {
            $stripe = new \Stripe\StripeClient(
                $merchant->test_mode
                    ? config('services.stripe.test_secret')
                    : config('services.stripe.secret')
            );

            $opts = [];
            if ($merchant->processor_account_id) {
                $opts['stripe_account'] = $merchant->processor_account_id;
            }

            // Get the payout details from Stripe
            if ($payout->processor_payout_id) {
                $stripePayout = $stripe->payouts->retrieve($payout->processor_payout_id, null, $opts ?: null);

                // Get balance transactions for this payout
                $balanceTxns = $stripe->balanceTransactions->all([
                    'payout' => $payout->processor_payout_id,
                    'limit' => 100,
                ], $opts ?: null);

                $transactions = collect($balanceTxns->data)->map(function ($bt) {
                    return [
                        'id' => $bt->id,
                        'type' => $bt->type,
                        'amount' => $bt->amount,
                        'fee' => $bt->fee,
                        'net' => $bt->net,
                        'currency' => strtoupper($bt->currency),
                        'description' => $bt->description,
                        'created' => \Carbon\Carbon::createFromTimestamp($bt->created),
                        'source' => $bt->source,
                    ];
                });

                // Calculate real fee from balance transactions
                $totalFee = $transactions->where('type', '!=', 'payout')->sum('fee');
                if ($totalFee > 0 && $payout->fee != $totalFee) {
                    $payout->update(['fee' => $totalFee, 'net_amount' => $payout->amount - $totalFee]);
                    $payout->refresh();
                }
            }
        } catch (\Exception $e) {
            // Stripe unavailable, show what we have
        }

        return view('dashboard.payouts.show', [
            'payout' => $payout,
            'transactions' => $transactions,
            'currency' => $merchant->default_currency ?? 'USD',
        ]);
    }
}
