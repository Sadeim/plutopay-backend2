<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    /**
     * Available webhook event types.
     */
    public const EVENT_TYPES = [
        'payment.created',
        'payment.completed',
        'payment.failed',
        'payment.refunded',
        'terminal.connected',
        'terminal.disconnected',
        'payout.initiated',
        'payout.completed',
        'dispute.created',
        'invoice.paid',
        'customer.created',
        'customer.updated',
    ];

    public function index()
    {
        $merchant = auth()->user()->merchant;

        $endpoints = WebhookEndpoint::where('merchant_id', $merchant->id)
            ->withCount('deliveries')
            ->orderByDesc('created_at')
            ->get();

        $recentEvents = WebhookEvent::where('merchant_id', $merchant->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $stats = [
            'total_endpoints' => $endpoints->count(),
            'active_endpoints' => $endpoints->where('status', 'active')->count(),
            'total_events' => WebhookEvent::where('merchant_id', $merchant->id)->count(),
            'failed_events' => WebhookEvent::where('merchant_id', $merchant->id)->where('status', 'failed')->count(),
        ];

        return view('dashboard.webhooks.index', [
            'endpoints' => $endpoints,
            'recentEvents' => $recentEvents,
            'stats' => $stats,
            'eventTypes' => self::EVENT_TYPES,
        ]);
    }

    /**
     * Create a new webhook endpoint.
     */
    public function store(Request $request)
    {
        $merchant = auth()->user()->merchant;

        $request->validate([
            'url' => 'required|url|max:500',
            'description' => 'nullable|string|max:255',
            'events' => 'nullable|array',
            'events.*' => 'string|in:' . implode(',', self::EVENT_TYPES),
        ]);

        $endpoint = WebhookEndpoint::create([
            'merchant_id' => $merchant->id,
            'url' => $request->url,
            'description' => $request->description,
            'secret' => 'whsec_' . Str::random(32),
            'events' => $request->events ?: [],
            'status' => 'active',
            'failure_count' => 0,
        ]);

        return redirect()->route('dashboard.webhooks.index')
            ->with('success', 'Webhook endpoint created successfully.')
            ->with('new_secret', $endpoint->secret)
            ->with('new_endpoint_id', $endpoint->id);
    }

    /**
     * Update a webhook endpoint.
     */
    public function update(Request $request, string $id)
    {
        $merchant = auth()->user()->merchant;
        $endpoint = WebhookEndpoint::where('merchant_id', $merchant->id)->findOrFail($id);

        $request->validate([
            'url' => 'required|url|max:500',
            'description' => 'nullable|string|max:255',
            'events' => 'nullable|array',
            'events.*' => 'string|in:' . implode(',', self::EVENT_TYPES),
        ]);

        $endpoint->update([
            'url' => $request->url,
            'description' => $request->description,
            'events' => $request->events ?: [],
        ]);

        return redirect()->route('dashboard.webhooks.index')
            ->with('success', 'Webhook endpoint updated.');
    }

    /**
     * Toggle endpoint status (active/disabled).
     */
    public function toggle(Request $request, string $id)
    {
        $merchant = auth()->user()->merchant;
        $endpoint = WebhookEndpoint::where('merchant_id', $merchant->id)->findOrFail($id);

        if ($endpoint->status === 'active') {
            $endpoint->update(['status' => 'disabled', 'disabled_at' => now()]);
        } else {
            $endpoint->update(['status' => 'active', 'disabled_at' => null, 'failure_count' => 0]);
        }

        return redirect()->route('dashboard.webhooks.index')
            ->with('success', 'Endpoint ' . ($endpoint->status === 'active' ? 'enabled' : 'disabled') . '.');
    }

    /**
     * Delete a webhook endpoint.
     */
    public function destroy(string $id)
    {
        $merchant = auth()->user()->merchant;
        $endpoint = WebhookEndpoint::where('merchant_id', $merchant->id)->findOrFail($id);
        $endpoint->delete();

        return redirect()->route('dashboard.webhooks.index')
            ->with('success', 'Webhook endpoint deleted.');
    }

    /**
     * Send a test webhook event.
     */
    public function test(string $id)
    {
        $merchant = auth()->user()->merchant;
        $endpoint = WebhookEndpoint::where('merchant_id', $merchant->id)->findOrFail($id);

        // Create a test event
        $event = WebhookEvent::create([
            'merchant_id' => $merchant->id,
            'type' => 'payment.completed',
            'payload' => json_encode([
                'id' => 'evt_test_' . Str::random(16),
                'type' => 'payment.completed',
                'created' => now()->timestamp,
                'data' => [
                    'object' => [
                        'id' => 'txn_test_' . Str::random(16),
                        'amount' => 5000,
                        'currency' => 'usd',
                        'status' => 'succeeded',
                    ],
                ],
            ]),
            'status' => 'pending',
            'is_test' => true,
        ]);

        return redirect()->route('dashboard.webhooks.index')
            ->with('success', 'Test event created. It will be delivered shortly.');
    }
}
