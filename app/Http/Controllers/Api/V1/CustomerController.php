<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerCollection;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        $query = Customer::where('merchant_id', $merchant->id)
            ->withCount('transactions')
            ->withSum(['transactions as total_spent' => function ($q) {
                $q->where('status', 'succeeded');
            }], 'amount');

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        // Filter by email
        if ($email = $request->input('email')) {
            $query->where('email', $email);
        }

        // Filter by country
        if ($country = $request->input('country')) {
            $query->where('country', $country);
        }

        // Sorting
        $sortField = $request->input('sortField', 'created_at');
        $sortOrder = $request->input('sortOrder', 'desc');

        $allowedSortFields = [
            'name', 'email', 'phone', 'country', 'created_at',
            'transactions_count', 'total_spent',
            '0' => 'name',
            '1' => 'email',
            '2' => 'phone',
            '3' => 'transactions_count',
            '4' => 'total_spent',
            '5' => 'created_at',
        ];

        if (isset($allowedSortFields[$sortField])) {
            $sortField = $allowedSortFields[$sortField];
        } elseif (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';
        $query->orderBy($sortField, $sortOrder);

        // Pagination
        $size = min(100, max(1, (int) $request->input('size', 10)));
        $paginated = $query->paginate($size, ['*'], 'page', $request->input('page', 1));

        return new CustomerCollection($paginated);
    }

    public function store(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        $validated = $request->validate([
            'email' => 'sometimes|email',
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:50',
            'external_id' => 'sometimes|string|max:255',
            'address_line1' => 'sometimes|string|max:255',
            'address_line2' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'country' => 'sometimes|string|max:2',
            'metadata' => 'sometimes|array',
        ]);

        $customer = Customer::create([
            'merchant_id' => $merchant->id,
            ...$validated,
        ]);

        return new CustomerResource($customer);
    }

    public function show(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');

        $customer = Customer::where('merchant_id', $merchant->id)
            ->withCount('transactions')
            ->withSum(['transactions as total_spent' => function ($q) {
                $q->where('status', 'succeeded');
            }], 'amount')
            ->findOrFail($id);

        return new CustomerResource($customer);
    }

    public function update(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');
        $customer = Customer::where('merchant_id', $merchant->id)->findOrFail($id);

        $validated = $request->validate([
            'email' => 'sometimes|email',
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:50',
            'external_id' => 'sometimes|string|max:255',
            'address_line1' => 'sometimes|string|max:255',
            'address_line2' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'country' => 'sometimes|string|max:2',
            'metadata' => 'sometimes|array',
        ]);

        $customer->update($validated);

        return new CustomerResource($customer);
    }

    public function destroy(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');
        $customer = Customer::where('merchant_id', $merchant->id)->findOrFail($id);
        $customer->delete();

        return response()->json(['deleted' => true]);
    }
}
