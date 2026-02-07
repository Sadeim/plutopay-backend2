<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    public function index()
    {
        $merchant = auth()->user()->merchant;

        $apiKeys = ApiKey::where('merchant_id', $merchant->id)
            ->orderByDesc('created_at')
            ->get();

        return view('dashboard.api-keys.index', [
            'apiKeys' => $apiKeys,
        ]);
    }

    /**
     * Create a new API key.
     */
    public function store(Request $request)
    {
        $merchant = auth()->user()->merchant;

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:publishable,secret',
        ]);

        $prefix = $request->type === 'secret' ? 'sk_' : 'pk_';
        $prefix .= $merchant->is_live ? 'live_' : 'test_';
        $rawKey = $prefix . Str::random(32);

        $apiKey = ApiKey::create([
            'merchant_id' => $merchant->id,
            'name' => $request->name,
            'type' => $request->type,
            'key' => $rawKey,
            'key_hash' => hash('sha256', $rawKey),
            'key_last_four' => substr($rawKey, -4),
            'is_test' => !$merchant->is_live,
            'created_by' => auth()->id(),
        ]);

        // Flash the raw key so user can copy it (shown only once)
        return redirect()->route('dashboard.api-keys.index')
            ->with('new_key', $rawKey)
            ->with('new_key_id', $apiKey->id)
            ->with('success', 'API key created successfully. Copy it now — it won\'t be shown again.');
    }

    /**
     * Revoke an API key.
     */
    public function revoke(Request $request, string $id)
    {
        $merchant = auth()->user()->merchant;

        $apiKey = ApiKey::where('merchant_id', $merchant->id)->findOrFail($id);

        if ($apiKey->isRevoked()) {
            return redirect()->route('dashboard.api-keys.index')
                ->with('error', 'This key is already revoked.');
        }

        $apiKey->update(['revoked_at' => now()]);

        return redirect()->route('dashboard.api-keys.index')
            ->with('success', 'API key revoked successfully.');
    }
}
