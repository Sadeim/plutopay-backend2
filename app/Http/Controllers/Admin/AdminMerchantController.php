<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MerchantUser;
use App\Models\Transaction;
use App\Models\Terminal;
use Illuminate\Http\Request;

class AdminMerchantController extends Controller
{
    public function index(Request $request)
    {
        $query = Merchant::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('business_name', 'ilike', "%{$request->search}%")
                  ->orWhere('email', 'ilike', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $merchants = $query->withCount(['transactions'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.merchants.index', compact('merchants'));
    }

    public function create()
    {
        return view('admin.merchants.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address_line1' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:2',
            'default_currency' => 'required|string|max:3',
            'processor_account_id' => 'nullable|string|max:255',
            'test_mode' => 'required|boolean',
            // First user
            'user_first_name' => 'required|string|max:255',
            'user_last_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255|unique:merchant_users,email',
            'user_password' => 'required|string|min:6',
        ]);

        $merchant = Merchant::create([
            'business_name' => $request->business_name,
            'display_name' => $request->display_name ?? $request->business_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'website' => $request->website,
            'address_line1' => $request->address_line1,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country ?? 'US',
            'default_currency' => $request->default_currency,
            'processor_account_id' => $request->processor_account_id,
            'test_mode' => $request->test_mode,
            'status' => 'active',
        ]);

        MerchantUser::create([
            'merchant_id' => $merchant->id,
            'first_name' => $request->user_first_name,
            'last_name' => $request->user_last_name,
            'email' => $request->user_email,
            'password' => bcrypt($request->user_password),
            'role' => 'owner',
        ]);

        return redirect()->route('admin.merchants.show', $merchant->id)
            ->with('success', 'Merchant created successfully!');
    }

    public function show(string $id)
    {
        $merchant = Merchant::findOrFail($id);

        $stats = [
            'total_volume' => Transaction::where('merchant_id', $id)->where('status', 'succeeded')->sum('amount'),
            'total_transactions' => Transaction::where('merchant_id', $id)->count(),
            'successful' => Transaction::where('merchant_id', $id)->where('status', 'succeeded')->count(),
            'terminals' => Terminal::where('merchant_id', $id)->count(),
            'users' => MerchantUser::where('merchant_id', $id)->count(),
        ];

        $transactions = Transaction::where('merchant_id', $id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $users = MerchantUser::where('merchant_id', $id)->get();
        $terminals = Terminal::where('merchant_id', $id)->get();

        return view('admin.merchants.show', compact('merchant', 'stats', 'transactions', 'users', 'terminals'));
    }

    public function edit(string $id)
    {
        $merchant = Merchant::findOrFail($id);
        $users = MerchantUser::where('merchant_id', $id)->get();
        return view('admin.merchants.edit', compact('merchant', 'users'));
    }

    public function update(Request $request, string $id)
    {
        $merchant = Merchant::findOrFail($id);

        $request->validate([
            'business_name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address_line1' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:2',
            'default_currency' => 'required|string|max:3',
            'processor_account_id' => 'nullable|string|max:255',
            'test_mode' => 'required|boolean',
            'status' => 'required|in:active,suspended,inactive',
        ]);

        $merchant->update($request->only([
            'business_name', 'display_name', 'email', 'phone', 'website',
            'address_line1', 'city', 'state', 'postal_code', 'country',
            'default_currency', 'processor_account_id', 'test_mode', 'status',
        ]));

        return redirect()->route('admin.merchants.show', $merchant->id)
            ->with('success', 'Merchant updated successfully!');
    }
}
