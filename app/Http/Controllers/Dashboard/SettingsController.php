<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MerchantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $merchant = $user->merchant;
        $teamMembers = MerchantUser::where('merchant_id', $merchant->id)
            ->orderByDesc('created_at')
            ->get();

        return view('dashboard.settings.index', [
            'merchant' => $merchant,
            'user' => $user,
            'teamMembers' => $teamMembers,
        ]);
    }

    /**
     * Update business profile.
     */
    public function updateBusiness(Request $request)
    {
        $merchant = auth()->user()->merchant;

        $request->validate([
            'business_name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:2',
            'default_currency' => 'nullable|string|max:3',
            'timezone' => 'nullable|string|max:50',
        ]);

        $merchant->update($request->only([
            'business_name', 'display_name', 'email', 'phone', 'website',
            'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country',
            'default_currency', 'timezone', 'processor_account_id',
        ]));

        return redirect()->route('dashboard.settings.index')
            ->with('success', 'Business profile updated.');
    }

    /**
     * Update personal profile.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:merchant_users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
        ]);

        $user->update($request->only(['first_name', 'last_name', 'email', 'phone']));

        return redirect()->route('dashboard.settings.index')
            ->with('success', 'Profile updated.');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('dashboard.settings.index')
            ->with('success', 'Password changed successfully.');
    }
}
