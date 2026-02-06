<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MerchantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:merchant_users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create Merchant
        $merchant = Merchant::create([
            'business_name' => $request->business_name,
            'email' => $request->email,
            'status' => 'active',
            'kyc_status' => 'not_started',
            'test_mode' => true,
        ]);

        // Create Owner User
        $user = MerchantUser::create([
            'merchant_id' => $merchant->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'owner',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Generate API Keys
        $testSecretKey = 'sk_test_' . Str::random(32);
        $testPubKey = 'pk_test_' . Str::random(32);

        $merchant->apiKeys()->createMany([
            [
                'name' => 'Test Secret Key',
                'type' => 'secret',
                'key' => $testSecretKey,
                'key_hash' => hash('sha256', $testSecretKey),
                'key_last_four' => substr($testSecretKey, -4),
                'is_test' => true,
                'created_by' => $user->id,
            ],
            [
                'name' => 'Test Publishable Key',
                'type' => 'publishable',
                'key' => $testPubKey,
                'key_hash' => hash('sha256', $testPubKey),
                'key_last_four' => substr($testPubKey, -4),
                'is_test' => true,
                'created_by' => $user->id,
            ],
        ]);

        $token = $user->createToken('dashboard')->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'merchant' => [
                'id' => $merchant->id,
                'business_name' => $merchant->business_name,
            ],
            'api_keys' => [
                'test_secret_key' => $testSecretKey,
                'test_publishable_key' => $testPubKey,
            ],
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = MerchantUser::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => ['type' => 'authentication_error', 'message' => 'Invalid credentials.']
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'error' => ['type' => 'authentication_error', 'message' => 'Account is not active.']
            ], 403);
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $token = $user->createToken('dashboard')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'merchant' => [
                'id' => $user->merchant->id,
                'business_name' => $user->merchant->business_name,
                'test_mode' => $user->merchant->test_mode,
            ],
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'avatar_url' => $user->avatar_url,
            ],
            'merchant' => [
                'id' => $user->merchant->id,
                'business_name' => $user->merchant->business_name,
                'status' => $user->merchant->status,
                'test_mode' => $user->merchant->test_mode,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }
}
