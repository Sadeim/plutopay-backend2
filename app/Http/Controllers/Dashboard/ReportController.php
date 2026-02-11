<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Payout;
use App\Models\Refund;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $merchant = auth()->user()->merchant;

        return view('dashboard.reports.index', [
            'merchant' => $merchant,
        ]);
    }

    public function generate(Request $request)
    {
        $merchant = auth()->user()->merchant;
        $reportType = $request->input('report_type', 'summary');
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $format = $request->input('format', 'view');

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        $data = match ($reportType) {
            'summary' => $this->summaryReport($merchant, $from, $to),
            'transactions' => $this->transactionsReport($merchant, $from, $to),
            'payouts' => $this->payoutsReport($merchant, $from, $to),
            'daily' => $this->dailyReport($merchant, $from, $to),
            'payment_methods' => $this->paymentMethodsReport($merchant, $from, $to),
            default => $this->summaryReport($merchant, $from, $to),
        };

        if ($format === 'csv') {
            return $this->exportCsv($reportType, $data, $merchant, $from, $to);
        }

        if ($request->ajax()) {
            return response()->json($data);
        }

        return view('dashboard.reports.show', [
            'merchant' => $merchant,
            'reportType' => $reportType,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'data' => $data,
        ]);
    }

    protected function summaryReport($merchant, $from, $to): array
    {
        $txns = Transaction::where('merchant_id', $merchant->id)
            ->whereBetween('created_at', [$from, $to]);

        $succeeded = (clone $txns)->where('status', 'succeeded');
        $failed = (clone $txns)->where('status', 'failed');
        $refunded = (clone $txns)->whereIn('status', ['refunded', 'partially_refunded']);

        $payouts = Payout::where('merchant_id', $merchant->id)
            ->whereBetween('created_at', [$from, $to]);

        $totalVolume = (clone $succeeded)->sum('amount');
        $totalRefunds = (clone $txns)->sum('amount_refunded');

        return [
            'title' => 'Summary Report',
            'overview' => [
                'total_transactions' => (clone $txns)->count(),
                'succeeded_count' => (clone $succeeded)->count(),
                'failed_count' => (clone $failed)->count(),
                'canceled_count' => (clone $txns)->where('status', 'canceled')->count(),
                'total_volume' => $totalVolume,
                'average_transaction' => (clone $succeeded)->count() > 0
                    ? round($totalVolume / (clone $succeeded)->count())
                    : 0,
                'total_refunds' => $totalRefunds,
                'net_volume' => $totalVolume - $totalRefunds,
                'total_payouts' => (clone $payouts)->count(),
                'total_paid_out' => (clone $payouts)->where('status', 'paid')->sum('amount'),
                'success_rate' => (clone $txns)->count() > 0
                    ? round(((clone $succeeded)->count() / (clone $txns)->count()) * 100, 1)
                    : 0,
            ],
        ];
    }

    protected function transactionsReport($merchant, $from, $to): array
    {
        $txns = Transaction::where('merchant_id', $merchant->id)
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get();

        return [
            'title' => 'Transactions Report',
            'transactions' => $txns,
            'totals' => [
                'count' => $txns->count(),
                'total_amount' => $txns->where('status', 'succeeded')->sum('amount'),
                'total_refunded' => $txns->sum('amount_refunded'),
            ],
        ];
    }

    protected function payoutsReport($merchant, $from, $to): array
    {
        $payouts = Payout::where('merchant_id', $merchant->id)
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get();

        return [
            'title' => 'Payouts Report',
            'payouts' => $payouts,
            'totals' => [
                'count' => $payouts->count(),
                'total_amount' => $payouts->sum('amount'),
                'total_fees' => $payouts->sum('fee'),
                'total_net' => $payouts->sum('net_amount'),
                'paid_count' => $payouts->where('status', 'paid')->count(),
                'in_transit_count' => $payouts->where('status', 'in_transit')->count(),
            ],
        ];
    }

    protected function dailyReport($merchant, $from, $to): array
    {
        $txns = Transaction::where('merchant_id', $merchant->id)
            ->where('status', 'succeeded')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($t) => $t->created_at->format('Y-m-d'));

        $days = [];
        $current = $from->copy();
        while ($current <= $to) {
            $key = $current->format('Y-m-d');
            $dayTxns = $txns->get($key, collect());
            $days[] = [
                'date' => $key,
                'date_formatted' => $current->format('M d, Y'),
                'count' => $dayTxns->count(),
                'volume' => $dayTxns->sum('amount'),
                'avg' => $dayTxns->count() > 0 ? round($dayTxns->sum('amount') / $dayTxns->count()) : 0,
            ];
            $current->addDay();
        }

        return [
            'title' => 'Daily Breakdown',
            'days' => $days,
            'totals' => [
                'total_days' => count($days),
                'active_days' => collect($days)->where('count', '>', 0)->count(),
                'total_volume' => collect($days)->sum('volume'),
                'total_count' => collect($days)->sum('count'),
                'best_day' => collect($days)->sortByDesc('volume')->first(),
            ],
        ];
    }

    protected function paymentMethodsReport($merchant, $from, $to): array
    {
        $txns = Transaction::where('merchant_id', $merchant->id)
            ->where('status', 'succeeded')
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->groupBy('payment_method_type');

        $methods = [];
        $totalVolume = 0;
        foreach ($txns as $method => $group) {
            $vol = $group->sum('amount');
            $totalVolume += $vol;
            $methods[] = [
                'method' => $method ?: 'Unknown',
                'method_label' => ucfirst(str_replace('_', ' ', $method ?: 'unknown')),
                'count' => $group->count(),
                'volume' => $vol,
                'avg' => $group->count() > 0 ? round($vol / $group->count()) : 0,
            ];
        }

        // Add percentage
        foreach ($methods as &$m) {
            $m['percentage'] = $totalVolume > 0 ? round(($m['volume'] / $totalVolume) * 100, 1) : 0;
        }

        usort($methods, fn ($a, $b) => $b['volume'] <=> $a['volume']);

        return [
            'title' => 'Payment Methods Breakdown',
            'methods' => $methods,
            'totals' => [
                'total_volume' => $totalVolume,
                'total_count' => collect($methods)->sum('count'),
                'method_count' => count($methods),
            ],
        ];
    }

    protected function exportCsv(string $type, array $data, $merchant, $from, $to)
    {
        $filename = "PlutoPay_{$type}_" . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv';
        $currencySymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'ILS' => '₪'];
        $currency = $merchant->default_currency ?? 'USD';
        $symbol = $currencySymbols[$currency] ?? $currency . ' ';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($type, $data, $merchant, $from, $to, $symbol) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['PlutoPay - ' . ($data['title'] ?? ucfirst($type) . ' Report')]);
            fputcsv($file, ['Merchant: ' . $merchant->business_name]);
            fputcsv($file, ['Period: ' . $from->format('M d, Y') . ' - ' . $to->format('M d, Y')]);
            fputcsv($file, ['Generated: ' . now()->format('M d, Y \a\t H:i')]);
            fputcsv($file, []);

            match ($type) {
                'summary' => $this->exportSummaryCsv($file, $data, $symbol),
                'transactions' => $this->exportTransactionsCsv($file, $data, $symbol),
                'payouts' => $this->exportPayoutsCsv($file, $data, $symbol),
                'daily' => $this->exportDailyCsv($file, $data, $symbol),
                'payment_methods' => $this->exportMethodsCsv($file, $data, $symbol),
                default => null,
            };

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportSummaryCsv($file, $data, $symbol): void
    {
        $o = $data['overview'];
        fputcsv($file, ['Metric', 'Value']);
        fputcsv($file, ['Total Transactions', $o['total_transactions']]);
        fputcsv($file, ['Succeeded', $o['succeeded_count']]);
        fputcsv($file, ['Failed', $o['failed_count']]);
        fputcsv($file, ['Success Rate', $o['success_rate'] . '%']);
        fputcsv($file, ['Total Volume', $symbol . number_format($o['total_volume'] / 100, 2)]);
        fputcsv($file, ['Average Transaction', $symbol . number_format($o['average_transaction'] / 100, 2)]);
        fputcsv($file, ['Total Refunds', $symbol . number_format($o['total_refunds'] / 100, 2)]);
        fputcsv($file, ['Net Volume', $symbol . number_format($o['net_volume'] / 100, 2)]);
        fputcsv($file, ['Total Payouts', $o['total_payouts']]);
        fputcsv($file, ['Total Paid Out', $symbol . number_format($o['total_paid_out'] / 100, 2)]);
    }

    protected function exportTransactionsCsv($file, $data, $symbol): void
    {
        fputcsv($file, ['Reference', 'Date', 'Amount', 'Status', 'Method', 'Card Brand', 'Card Last 4', 'Customer Email', 'Description']);
        foreach ($data['transactions'] as $t) {
            fputcsv($file, [
                $t->reference,
                $t->created_at->format('Y-m-d H:i:s'),
                number_format($t->amount / 100, 2),
                ucfirst($t->status),
                ucfirst(str_replace('_', ' ', $t->payment_method_type ?? '')),
                ucfirst($t->card_brand ?? ''),
                $t->card_last_four ?? '',
                $t->receipt_email ?? '',
                $t->description ?? '',
            ]);
        }
    }

    protected function exportPayoutsCsv($file, $data, $symbol): void
    {
        fputcsv($file, ['Reference', 'Date', 'Amount', 'Fee', 'Net', 'Status', 'Arrival Date']);
        foreach ($data['payouts'] as $p) {
            fputcsv($file, [
                $p->reference,
                $p->created_at->format('Y-m-d H:i:s'),
                number_format($p->amount / 100, 2),
                number_format(($p->fee ?? 0) / 100, 2),
                number_format(($p->net_amount ?? $p->amount) / 100, 2),
                ucfirst($p->status),
                $p->estimated_arrival_at ? $p->estimated_arrival_at->format('Y-m-d') : '',
            ]);
        }
    }

    protected function exportDailyCsv($file, $data, $symbol): void
    {
        fputcsv($file, ['Date', 'Transactions', 'Volume', 'Average']);
        foreach ($data['days'] as $d) {
            fputcsv($file, [
                $d['date'],
                $d['count'],
                number_format($d['volume'] / 100, 2),
                number_format($d['avg'] / 100, 2),
            ]);
        }
    }

    protected function exportMethodsCsv($file, $data, $symbol): void
    {
        fputcsv($file, ['Payment Method', 'Transactions', 'Volume', 'Average', 'Percentage']);
        foreach ($data['methods'] as $m) {
            fputcsv($file, [
                $m['method_label'],
                $m['count'],
                number_format($m['volume'] / 100, 2),
                number_format($m['avg'] / 100, 2),
                $m['percentage'] . '%',
            ]);
        }
    }
}
