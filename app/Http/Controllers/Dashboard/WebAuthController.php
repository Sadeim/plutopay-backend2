<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MerchantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($request->only('email', 'password'), true)) {
            $request->session()->regenerate();
            $user = Auth::user();
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:merchant_users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $merchant = Merchant::create([
            'business_name' => $request->business_name,
            'email' => $request->email,
            'status' => 'active',
            'kyc_status' => 'not_started',
            'test_mode' => true,
        ]);

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

        $testSecretKey = 'sk_test_' . Str::random(32);
        $testPubKey = 'pk_test_' . Str::random(32);

        $merchant->apiKeys()->createMany([
            [
                'name' => 'Test Secret Key', 'type' => 'secret',
                'key' => $testSecretKey, 'key_hash' => hash('sha256', $testSecretKey),
                'key_last_four' => substr($testSecretKey, -4), 'is_test' => true, 'created_by' => $user->id,
            ],
            [
                'name' => 'Test Publishable Key', 'type' => 'publishable',
                'key' => $testPubKey, 'key_hash' => hash('sha256', $testPubKey),
                'key_last_four' => substr($testPubKey, -4), 'is_test' => true, 'created_by' => $user->id,
            ],
        ]);

        Auth::login($user);
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
