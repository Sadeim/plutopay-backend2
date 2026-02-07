<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Transaction;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\TransactionCollection;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * List transactions.
     *
     * GET /v1/transactions
     */
    public function index(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        $query = Transaction::where('merchant_id', $merchant->id);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhere('receipt_email', 'ilike', "%{$search}%");
            });
        }

        // Filters
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($method = $request->input('payment_method_type')) {
            $query->where('payment_method_type', $method);
        }
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Sorting
        $sortField = $request->input('sortField', 'created_at');
        $sortOrder = $request->input('sortOrder', 'desc');
        $allowedFields = ['reference', 'amount', 'status', 'payment_method_type', 'created_at', 'currency'];
        $indexMap = ['0' => 'reference', '1' => 'amount', '2' => 'status', '3' => 'payment_method_type', '4' => 'receipt_email', '5' => 'created_at'];

        if (isset($indexMap[$sortField])) {
            $sortField = $indexMap[$sortField];
        } elseif (!in_array($sortField, $allowedFields)) {
            $sortField = 'created_at';
        }
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';
        $query->orderBy($sortField, $sortOrder);

        $size = min(100, max(1, (int) $request->input('size', 10)));
        $paginated = $query->paginate($size, ['*'], 'page', $request->input('page', 1));

        return new TransactionCollection($paginated);
    }

    /**
     * Create a payment.
     *
     * POST /v1/transactions
     *
     * Body:
     *   amount (required, integer, in cents)
     *   currency (string, default: merchant default)
     *   payment_method (string, Stripe PM id)
     *   payment_method_type (string: card, wallet, bank_transfer, terminal)
     *   confirm (boolean, default: false)
     *   capture_method (string: automatic|manual)
     *   customer_id (uuid)
     *   description (string)
     *   receipt_email (string)
     *   return_url (string)
     *   metadata (object)
     *   idempotency_key (string)
     *   billing_address (object)
     *   shipping_address (object)
     */
    public function store(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        $request->validate([
            'amount' => 'required|integer|min:50',
            'currency' => 'sometimes|string|size:3',
            'payment_method' => 'sometimes|string',
            'payment_method_type' => 'sometimes|string|in:card,wallet,bank_transfer,terminal',
            'confirm' => 'sometimes|boolean',
            'capture_method' => 'sometimes|string|in:automatic,manual',
            'customer_id' => 'sometimes|uuid',
            'description' => 'sometimes|string|max:500',
            'receipt_email' => 'sometimes|email',
            'return_url' => 'sometimes|url',
            'metadata' => 'sometimes|array',
            'idempotency_key' => 'sometimes|string|max:255',
            'billing_address' => 'sometimes|array',
            'shipping_address' => 'sometimes|array',
        ]);

        // Idempotency check
        if ($idempotencyKey = $request->input('idempotency_key')) {
            $existing = Transaction::where('merchant_id', $merchant->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return response()->json([
                    'data' => new TransactionResource($existing),
                    'message' => 'Idempotent request - returning existing transaction.',
                ], 200);
            }
        }

        try {
            $transaction = $this->paymentService->createPayment($merchant, $request->all());

            $response = new TransactionResource($transaction);
            $data = $response->toArray(request());

            // Include client_secret for frontend use
            if ($transaction->client_secret) {
                $data['client_secret'] = $transaction->client_secret;
            }

            return response()->json(['data' => $data], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'type' => 'payment_error',
                    'message' => $e->getMessage(),
                ]
            ], 400);
        }
    }

    /**
     * Show a transaction.
     *
     * GET /v1/transactions/{id}
     */
    public function show(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');

        $transaction = Transaction::where('merchant_id', $merchant->id)
            ->with('customer')
            ->findOrFail($id);

        return new TransactionResource($transaction);
    }

    /**
     * Capture an authorized payment.
     *
     * POST /v1/transactions/{id}/capture
     */
    public function capture(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');
        $transaction = Transaction::where('merchant_id', $merchant->id)->findOrFail($id);

        $request->validate([
            'amount' => 'sometimes|integer|min:1',
        ]);

        try {
            $transaction = $this->paymentService->capturePayment(
                $merchant,
                $transaction,
                $request->input('amount')
            );

            return new TransactionResource($transaction);

        } catch (\Exception $e) {
            return response()->json([
                'error' => ['type' => 'capture_error', 'message' => $e->getMessage()]
            ], 400);
        }
    }

    /**
     * Cancel a payment.
     *
     * POST /v1/transactions/{id}/cancel
     */
    public function cancel(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');
        $transaction = Transaction::where('merchant_id', $merchant->id)->findOrFail($id);

        try {
            $transaction = $this->paymentService->cancelPayment($merchant, $transaction);
            return new TransactionResource($transaction);

        } catch (\Exception $e) {
            return response()->json([
                'error' => ['type' => 'cancel_error', 'message' => $e->getMessage()]
            ], 400);
        }
    }
}
