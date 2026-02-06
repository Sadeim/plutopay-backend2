<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Terminal;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $merchant = $request->user()->merchant;

        $totalVolume = Transaction::where('merchant_id', $merchant->id)
            ->where('status', 'succeeded')
            ->sum('amount');

        $totalTransactions = Transaction::where('merchant_id', $merchant->id)->count();

        $successfulTransactions = Transaction::where('merchant_id', $merchant->id)
            ->where('status', 'succeeded')
            ->count();

        $successRate = $totalTransactions > 0
            ? ($successfulTransactions / $totalTransactions) * 100
            : 0;

        $activeTerminals = Terminal::where('merchant_id', $merchant->id)
            ->where('status', 'online')
            ->count();

        $recentTransactions = Transaction::where('merchant_id', $merchant->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboard.index', [
            'stats' => [
                'total_volume' => $totalVolume,
                'total_transactions' => $totalTransactions,
                'success_rate' => $successRate,
                'active_terminals' => $activeTerminals,
            ],
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
