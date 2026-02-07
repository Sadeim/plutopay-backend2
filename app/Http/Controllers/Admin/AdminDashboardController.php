<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\Terminal;
use App\Models\MerchantUser;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_merchants' => Merchant::count(),
            'active_merchants' => Merchant::where('status', 'active')->count(),
            'total_transactions' => Transaction::count(),
            'total_volume' => Transaction::where('status', 'succeeded')->sum('amount'),
            'total_terminals' => Terminal::count(),
            'online_terminals' => Terminal::where('status', 'online')->count(),
            'total_users' => MerchantUser::count(),
            'transactions_today' => Transaction::whereDate('created_at', today())->count(),
            'volume_today' => Transaction::whereDate('created_at', today())->where('status', 'succeeded')->sum('amount'),
            'transactions_30d' => Transaction::where('created_at', '>=', now()->subDays(30))->count(),
            'volume_30d' => Transaction::where('created_at', '>=', now()->subDays(30))->where('status', 'succeeded')->sum('amount'),
        ];

        $recentTransactions = Transaction::with('merchant')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $recentMerchants = Merchant::orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentTransactions', 'recentMerchants'));
    }
}
