<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $platformStats = [
            'total_merchants' => Merchant::count(),
            'live_merchants' => Merchant::where('test_mode', false)->count(),
            'test_merchants' => Merchant::where('test_mode', true)->count(),
        ];

        return view('admin.settings.index', compact('platformStats'));
    }
}
