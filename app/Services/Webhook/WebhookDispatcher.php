<?php

namespace App\Services\Webhook;

use App\Models\Merchant;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Http;

class WebhookDispatcher
{
    public static function dispatch(Merchant $merchant, string $eventType, array $data): WebhookEvent
    {
        $event = WebhookEvent::create([
            'merchant_id' => $merchant->id,
            'type' => $eventType,
            'payload' => [
                'id' => $eventType . '_' . now()->timestamp,
                'type' => $eventType,
                'created_at' => now()->toIso8601String(),
                'data' => $data,
            ],
            'status' => 'pending',
            'is_test' => $merchant->test_mode,
        ]);

        $endpoints = WebhookEndpoint::where('merchant_id', $merchant->id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->get();

        foreach ($endpoints as $endpoint) {
            if (!$endpoint->subscribesTo($eventType)) continue;

            static::deliver($event, $endpoint);
        }

        $event->update(['status' => 'delivered']);

        return $event;
    }

    public static function deliver(WebhookEvent $event, WebhookEndpoint $endpoint, int $attempt = 1): WebhookDelivery
    {
        $payload = json_encode($event->payload);
        $signature = static::sign($payload, $endpoint->secret);
        $timeout = config('plutopay.webhook_timeout', 30);

        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'PlutoPay-Webhook/1.0',
            'X-PlutoPay-Event' => $event->type,
            'X-PlutoPay-Delivery' => $event->id,
            'X-PlutoPay-Signature' => $signature,
            'X-PlutoPay-Timestamp' => (string) now()->timestamp,
        ];

        $delivery = WebhookDelivery::create([
            'webhook_event_id' => $event->id,
            'webhook_endpoint_id' => $endpoint->id,
            'url' => $endpoint->url,
            'request_headers' => json_encode($headers),
            'request_body' => $payload,
            'attempt_number' => $attempt,
            'status' => 'pending',
        ]);

        try {
            $startTime = microtime(true);

            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->withBody($payload, 'application/json')
                ->post($endpoint->url);

            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            $delivery->update([
                'http_status' => $response->status(),
                'response_headers' => json_encode($response->headers()),
                'response_body' => substr($response->body(), 0, 5000),
                'response_time_ms' => $responseTime,
                'status' => $response->successful() ? 'delivered' : 'failed',
                'delivered_at' => $response->successful() ? now() : null,
            ]);

            if ($response->successful()) {
                $endpoint->update([
                    'failure_count' => 0,
                    'last_success_at' => now(),
                ]);
            } else {
                static::handleFailure($delivery, $endpoint, $attempt);
            }

        } catch (\Exception $e) {
            $delivery->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'response_time_ms' => (int) ((microtime(true) - ($startTime ?? microtime(true))) * 1000),
            ]);

            static::handleFailure($delivery, $endpoint, $attempt);
        }

        return $delivery;
    }

    public static function retry(WebhookDelivery $delivery): WebhookDelivery
    {
        $event = $delivery->event;
        $endpoint = $delivery->endpoint;

        return static::deliver($event, $endpoint, $delivery->attempt_number + 1);
    }

    private static function handleFailure(WebhookDelivery $delivery, WebhookEndpoint $endpoint, int $attempt): void
    {
        $maxRetries = config('plutopay.webhook_max_retries', 5);

        $endpoint->increment('failure_count');
        $endpoint->update(['last_failure_at' => now()]);

        // Schedule retry with exponential backoff
        if ($attempt < $maxRetries) {
            $delays = [60, 300, 1800, 7200, 86400]; // 1min, 5min, 30min, 2hr, 24hr
            $delay = $delays[$attempt - 1] ?? 86400;

            $delivery->update([
                'next_retry_at' => now()->addSeconds($delay),
            ]);
        }

        // Disable endpoint after too many failures
        if ($endpoint->failure_count >= 10) {
            $endpoint->update([
                'status' => 'disabled',
                'disabled_at' => now(),
            ]);
        }
    }

    public static function sign(string $payload, string $secret): string
    {
        $timestamp = now()->timestamp;
        $signedPayload = "{$timestamp}.{$payload}";
        $signature = hash_hmac('sha256', $signedPayload, $secret);

        return "t={$timestamp},v1={$signature}";
    }

    public static function verifySignature(string $payload, string $signature, string $secret, int $tolerance = 300): bool
    {
        $parts = [];
        foreach (explode(',', $signature) as $part) {
            [$key, $value] = explode('=', $part, 2);
            $parts[$key] = $value;
        }

        if (!isset($parts['t']) || !isset($parts['v1'])) return false;

        $timestamp = (int) $parts['t'];
        if (abs(time() - $timestamp) > $tolerance) return false;

        $signedPayload = "{$timestamp}.{$payload}";
        $expected = hash_hmac('sha256', $signedPayload, $secret);

        return hash_equals($expected, $parts['v1']);
    }
}
