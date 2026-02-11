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
}
