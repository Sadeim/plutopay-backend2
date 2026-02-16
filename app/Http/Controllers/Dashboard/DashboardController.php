<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Terminal;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $merchant = $request->user()->merchant;
        $mid = $merchant->id;

        // ── Main Stats ──
        $totalVolume = Transaction::where('merchant_id', $mid)
            ->where('status', 'succeeded')->sum('amount');

        $totalTips = Transaction::where('merchant_id', $mid)
            ->where('status', 'succeeded')->sum('tip_amount');

        $totalTransactions = Transaction::where('merchant_id', $mid)->count();

        $successfulTransactions = Transaction::where('merchant_id', $mid)
            ->where('status', 'succeeded')->count();

        $successRate = $totalTransactions > 0
            ? ($successfulTransactions / $totalTransactions) * 100 : 0;

        $activeTerminals = Terminal::where('merchant_id', $mid)
            ->where('status', 'online')->count();

        // ── 30-day stats for comparison ──
        $last30 = now()->subDays(30);
        $prev30 = now()->subDays(60);

        $volume30d = Transaction::where('merchant_id', $mid)
            ->where('status', 'succeeded')
            ->where('created_at', '>=', $last30)->sum('amount');

        $volumePrev30d = Transaction::where('merchant_id', $mid)
            ->where('status', 'succeeded')
            ->whereBetween('created_at', [$prev30, $last30])->sum('amount');

        $txn30d = Transaction::where('merchant_id', $mid)
            ->where('created_at', '>=', $last30)->count();

        $txnPrev30d = Transaction::where('merchant_id', $mid)
            ->whereBetween('created_at', [$prev30, $last30])->count();

        $customers30d = Customer::where('merchant_id', $mid)
            ->where('created_at', '>=', $last30)->count();

        // ── Daily volume (last 14 days) for chart ──
        $dailyVolume = Transaction::where('merchant_id', $mid)
            ->where('status', 'succeeded')
            ->where('created_at', '>=', now()->subDays(14))
            ->select(
                DB::raw("DATE(created_at) as date"),
                DB::raw("SUM(amount) as total"),
                DB::raw("COUNT(*) as count")
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill missing days
        $chartData = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $day = $dailyVolume->firstWhere('date', $date);
            $chartData[] = [
                'date' => now()->subDays($i)->format('M d'),
                'volume' => $day ? round($day->total / 100, 2) : 0,
                'count' => $day ? $day->count : 0,
            ];
        }

        // ── Payment methods breakdown ──
        $methodBreakdown = Transaction::where('merchant_id', $mid)
            ->where('status', 'succeeded')
            ->select('payment_method_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method_type')
            ->get();

        // ── Status breakdown ──
        $statusBreakdown = Transaction::where('merchant_id', $mid)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // ── Recent Transactions ──
        $recentTransactions = Transaction::where('merchant_id', $mid)
            ->with('customer')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // ── Recent Payouts ──
        $recentPayouts = Payout::where('merchant_id', $mid)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // ── Totals ──
        $totalCustomers = Customer::where('merchant_id', $mid)->count();
        $totalPayoutsAmount = Payout::where('merchant_id', $mid)->where('status', 'paid')->sum('amount');

        return view('dashboard.index', [
            'stats' => [
                'total_volume' => $totalVolume,
            'total_tips' => $totalTips,
                'total_transactions' => $totalTransactions,
                'success_rate' => $successRate,
                'active_terminals' => $activeTerminals,
                'total_customers' => $totalCustomers,
                'total_payouts' => $totalPayoutsAmount,
                'volume_30d' => $volume30d,
                'volume_prev_30d' => $volumePrev30d,
                'txn_30d' => $txn30d,
                'txn_prev_30d' => $txnPrev30d,
                'customers_30d' => $customers30d,
            ],
            'chartData' => $chartData,
            'methodBreakdown' => $methodBreakdown,
            'statusBreakdown' => $statusBreakdown,
            'recentTransactions' => $recentTransactions,
            'recentPayouts' => $recentPayouts,
            'currency' => $merchant->default_currency ?? 'USD',
        ]);
    }
}
