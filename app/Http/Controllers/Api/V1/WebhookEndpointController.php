<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WebhookEndpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookEndpointController extends Controller
{
    public function index(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        $endpoints = WebhookEndpoint::where('merchant_id', $merchant->id)
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json($endpoints);
    }

    public function store(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        $request->validate([
            'url' => 'required|url|max:500',
            'events' => 'sometimes|array',
            'events.*' => 'string',
            'description' => 'sometimes|string|max:500',
        ]);

        $endpoint = WebhookEndpoint::create([
            'merchant_id' => $merchant->id,
            'url' => $request->url,
            'secret' => 'whsec_' . Str::random(32),
            'events' => $request->events,
            'status' => 'active',
            'description' => $request->description,
        ]);

        // Return with secret visible (only on creation)
        return response()->json([
            'id' => $endpoint->id,
            'url' => $endpoint->url,
            'secret' => $endpoint->secret,
            'events' => $endpoint->events,
            'status' => $endpoint->status,
            'description' => $endpoint->description,
            'created_at' => $endpoint->created_at->toIso8601String(),
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');
        $endpoint = WebhookEndpoint::where('merchant_id', $merchant->id)->findOrFail($id);
        return response()->json($endpoint);
    }

    public function update(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');
        $endpoint = WebhookEndpoint::where('merchant_id', $merchant->id)->findOrFail($id);

        $request->validate([
            'url' => 'sometimes|url|max:500',
            'events' => 'sometimes|array',
            'status' => 'sometimes|in:active,disabled',
            'description' => 'sometimes|string|max:500',
        ]);

        $endpoint->update($request->only(['url', 'events', 'status', 'description']));

        if ($request->status === 'disabled') {
            $endpoint->update(['disabled_at' => now()]);
        }

        return response()->json($endpoint);
    }

    public function destroy(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');
        $endpoint = WebhookEndpoint::where('merchant_id', $merchant->id)->findOrFail($id);
        $endpoint->delete();
        return response()->json(['deleted' => true]);
    }
}
