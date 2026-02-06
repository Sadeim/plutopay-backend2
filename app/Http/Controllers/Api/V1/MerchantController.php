<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function show(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        return response()->json([
            'id' => $merchant->id,
            'business_name' => $merchant->business_name,
            'display_name' => $merchant->display_name,
            'email' => $merchant->email,
            'phone' => $merchant->phone,
            'country' => $merchant->country,
            'default_currency' => $merchant->default_currency,
            'status' => $merchant->status,
            'kyc_status' => $merchant->kyc_status,
            'test_mode' => $merchant->test_mode,
            'created_at' => $merchant->created_at->toIso8601String(),
        ]);
    }
}
