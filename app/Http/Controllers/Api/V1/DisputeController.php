<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        $disputes = Dispute::where('merchant_id', $merchant->id)
            ->when($request->input('status'), fn($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json($disputes);
    }

    public function show(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');
        $dispute = Dispute::where('merchant_id', $merchant->id)->findOrFail($id);
        return response()->json($dispute);
    }
}
