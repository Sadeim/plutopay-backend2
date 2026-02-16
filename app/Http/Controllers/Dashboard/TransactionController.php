<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $merchant = auth()->user()->merchant;

        // Stats
        $stats = [
            'total_count' => Transaction::where('merchant_id', $merchant->id)->count(),
            'succeeded'   => Transaction::where('merchant_id', $merchant->id)->where('status', 'succeeded')->count(),
            'pending'     => Transaction::where('merchant_id', $merchant->id)->where('status', 'pending')->count(),
            'failed'      => Transaction::where('merchant_id', $merchant->id)->where('status', 'failed')->count(),
        ];

        // Handle AJAX requests for datatable
        if ($request->ajax() || $request->wantsJson()) {
            $query = Transaction::where('merchant_id', $merchant->id);

            // Search
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference', 'ilike', "%{$search}%")
                      ->orWhere('receipt_email', 'ilike', "%{$search}%")
                      ->orWhere('description', 'ilike', "%{$search}%")
                      ->orWhere('processor_transaction_id', 'ilike', "%{$search}%");
                });
            }

            // Filters
            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }
            if ($method = $request->input('payment_method_type')) {
                $query->where('payment_method_type', $method);
            }

            // Date range filters
            if ($dateFrom = $request->input('date_from')) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo = $request->input('date_to')) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            // Amount range filters (values come in cents)
            if ($amountMin = $request->input('amount_min')) {
                $query->where('amount', '>=', (int)$amountMin);
            }
            if ($amountMax = $request->input('amount_max')) {
                $query->where('amount', '<=', (int)$amountMax);
            }

            // Date range filters
            if ($dateFrom = $request->input('date_from')) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo = $request->input('date_to')) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            // Amount range filters (values come in cents)
            if ($amountMin = $request->input('amount_min')) {
                $query->where('amount', '>=', (int)$amountMin);
            }
            if ($amountMax = $request->input('amount_max')) {
                $query->where('amount', '<=', (int)$amountMax);
            }

            // Sort
            $sortField = $request->input('sortField', 'created_at');
            $sortOrder = $request->input('sortOrder', 'desc');
            $allowed = ['reference', 'amount', 'status', 'payment_method_type', 'receipt_email', 'created_at'];
            if (in_array($sortField, $allowed)) {
                $query->orderBy($sortField, $sortOrder === 'asc' ? 'asc' : 'desc');
            }

            // Paginate
            $size = min((int) $request->input('size', 10), 100);
            $page = max((int) $request->input('page', 1), 1);
            $total = $query->count();
            $rows = $query->skip(($page - 1) * $size)->take($size)->get();

            // Format
            $data = $rows->map(function ($t) {
                $statusBadge = match($t->status) {
                    'succeeded' => 'success',
                    'pending', 'requires_capture' => 'warning',
                    'failed' => 'destructive',
                    'refunded', 'partially_refunded' => 'info',
                    'canceled' => 'secondary',
                    default => 'secondary',
                };
                return [
                    'id' => $t->id,
                    'reference' => $t->reference,
                    'amount' => $t->amount,
                    'currency' => $t->currency,
                    'amount_formatted' => $t->formatted_amount ?? ('$' . number_format($t->amount / 100, 2)),
                    'status' => $t->status,
                    'status_badge' => $statusBadge,
                    'payment_method_type' => $t->payment_method_type,
                    'receipt_email' => $t->receipt_email,
                    'description' => $t->description,
                    'tip_amount' => $t->tip_amount ?? 0,
                    'created_at' => $t->created_at?->toIso8601String(),
                ];
            });

            return response()->json([
                'data' => $data,
                'totalCount' => $total,
                'page' => $page,
                'lastPage' => max(1, ceil($total / $size)),
            ]);
        }

        return view('dashboard.transactions.index', [
            'stats' => $stats,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $merchant = auth()->user()->merchant;
        $query = Transaction::where('merchant_id', $merchant->id);

        // Apply same filters as index
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'ilike', "%{$search}%")
                  ->orWhere('receipt_email', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }
        if ($status = $request->input('status')) $query->where('status', $status);
        if ($method = $request->input('payment_method_type')) $query->where('payment_method_type', $method);
        if ($dateFrom = $request->input('date_from')) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo = $request->input('date_to')) $query->whereDate('created_at', '<=', $dateTo);
        if ($amountMin = $request->input('amount_min')) $query->where('amount', '>=', (int)$amountMin);
        if ($amountMax = $request->input('amount_max')) $query->where('amount', '<=', (int)$amountMax);

        $transactions = $query->orderByDesc('created_at')->get();
        $currency = $merchant->default_currency ?? 'USD';
        $currencySymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'ILS' => '₪'];
        $symbol = $currencySymbols[$currency] ?? $currency . ' ';

        // Build CSV with BOM for Excel compatibility
        $filename = 'PlutoPay_Transactions_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transactions, $merchant, $symbol, $currency) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header info rows
            fputcsv($file, ['PlutoPay - Transaction Report']);
            fputcsv($file, ['Merchant: ' . $merchant->business_name]);
            fputcsv($file, ['Generated: ' . now()->format('M d, Y \a\t H:i')]);
            fputcsv($file, ['Total Transactions: ' . $transactions->count()]);
            $totalAmount = $transactions->where('status', 'succeeded')->sum('amount');
            fputcsv($file, ['Total Succeeded: ' . $symbol . number_format($totalAmount / 100, 2)]);
            fputcsv($file, []);

            // Column headers
            fputcsv($file, [
                'Reference',
                'Date',
                'Amount',
                'Currency',
                'Status',
                'Payment Method',
                'Card Brand',
                'Card Last 4',
                'Customer Email',
                'Description',
                'Processor ID',
            ]);

            // Data rows
            foreach ($transactions as $txn) {
                fputcsv($file, [
                    $txn->reference,
                    $txn->created_at->format('Y-m-d H:i:s'),
                    number_format($txn->amount / 100, 2),
                    strtoupper($txn->currency),
                    ucfirst($txn->status),
                    ucfirst(str_replace('_', ' ', $txn->payment_method_type ?? 'N/A')),
                    ucfirst($txn->card_brand ?? ''),
                    $txn->card_last_four ?? '',
                    $txn->receipt_email ?? '',
                    $txn->description ?? '',
                    $txn->processor_transaction_id ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
