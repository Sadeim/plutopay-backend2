<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next, string $type = 'secret')
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => ['type' => 'authentication_error', 'message' => 'Missing API key.']
            ], 401);
        }

        $apiKey = ApiKey::where('key_hash', hash('sha256', $token))
            ->whereNull('revoked_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$apiKey) {
            return response()->json([
                'error' => ['type' => 'authentication_error', 'message' => 'Invalid API key.']
            ], 401);
        }

        if ($type === 'secret' && $apiKey->type !== 'secret') {
            return response()->json([
                'error' => ['type' => 'authentication_error', 'message' => 'Secret key required.']
            ], 403);
        }

        $merchant = $apiKey->merchant;

        if (!$merchant || $merchant->status !== 'active') {
            return response()->json([
                'error' => ['type' => 'authentication_error', 'message' => 'Merchant account is not active.']
            ], 403);
        }

        // Update last used
        $apiKey->update(['last_used_at' => now()]);

        // Bind to request
        $request->merge([
            'merchant_id' => $merchant->id,
            'api_key_id' => $apiKey->id,
            'is_test' => $apiKey->is_test,
        ]);
        $request->attributes->set('merchant', $merchant);
        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }
}
