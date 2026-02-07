<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AdminTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('merchant');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('reference', 'ilike', "%{$request->search}%")
                  ->orWhere('processor_transaction_id', 'ilike', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->merchant_id) {
            $query->where('merchant_id', $request->merchant_id);
        }

        $transactions = $query->orderByDesc('created_at')->paginate(20);

        $stats = [
            'total' => Transaction::count(),
            'succeeded' => Transaction::where('status', 'succeeded')->count(),
            'pending' => Transaction::where('status', 'pending')->count(),
            'failed' => Transaction::where('status', 'failed')->count(),
            'total_volume' => Transaction::where('status', 'succeeded')->sum('amount'),
        ];

        return view('admin.transactions.index', compact('transactions', 'stats'));
    }
}
