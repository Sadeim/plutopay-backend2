<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        $customers = Customer::where('merchant_id', $merchant->id)
            ->when($request->input('email'), fn($q, $e) => $q->where('email', $e))
            ->when($request->input('search'), fn($q, $s) => $q->where(function($q) use ($s) {
                $q->where('name', 'ilike', "%{$s}%")
                  ->orWhere('email', 'ilike', "%{$s}%")
                  ->orWhere('phone', 'ilike', "%{$s}%");
            }))
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        $request->validate([
            'email' => 'sometimes|email',
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:50',
            'external_id' => 'sometimes|string|max:255',
            'metadata' => 'sometimes|array',
        ]);

        $customer = Customer::create([
            'merchant_id' => $merchant->id,
            ...$request->only(['email', 'name', 'phone', 'external_id', 'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country', 'metadata']),
        ]);

        return response()->json($customer, 201);
    }

    public function show(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');
        $customer = Customer::where('merchant_id', $merchant->id)->findOrFail($id);
        return response()->json($customer);
    }

    public function update(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');
        $customer = Customer::where('merchant_id', $merchant->id)->findOrFail($id);

        $customer->update($request->only([
            'email', 'name', 'phone', 'external_id',
            'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country', 'metadata',
        ]));

        return response()->json($customer);
    }

    public function destroy(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');
        $customer = Customer::where('merchant_id', $merchant->id)->findOrFail($id);
        $customer->delete();
        return response()->json(['deleted' => true]);
    }
}
