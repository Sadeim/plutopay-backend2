<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Webhook\WebhookDispatcher;
use Illuminate\Http\Request;

class WebhookTestController extends Controller
{
    public function test(Request $request, string $endpointId)
    {
        $merchant = $request->attributes->get('merchant');

        $endpoint = \App\Models\WebhookEndpoint::where('merchant_id', $merchant->id)
            ->findOrFail($endpointId);

        $event = WebhookDispatcher::dispatch($merchant, 'test.event', [
            'message' => 'This is a test webhook event from PlutoPay.',
            'endpoint_id' => $endpoint->id,
        ]);

        return response()->json([
            'message' => 'Test webhook sent.',
            'event_id' => $event->id,
            'event_type' => 'test.event',
        ]);
    }
}
