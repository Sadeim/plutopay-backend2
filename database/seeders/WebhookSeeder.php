<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use App\Models\WebhookDelivery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WebhookSeeder extends Seeder
{
    public function run(): void
    {
        $merchant = Merchant::first();

        if (!$merchant) {
            $this->command->error('No merchant found.');
            return;
        }

        $this->command->info("Creating webhook data for: {$merchant->business_name}");

        // Create 3 endpoints
        $endpoints = [];

        $endpoints[] = WebhookEndpoint::create([
            'merchant_id' => $merchant->id,
            'url' => 'https://api.mystore.com/webhooks/plutopay',
            'secret' => 'whsec_' . Str::random(32),
            'description' => 'Production webhook handler',
            'events' => ['payment.completed', 'payment.failed', 'payment.refunded'],
            'status' => 'active',
            'failure_count' => 0,
            'last_success_at' => now()->subMinutes(15),
            'created_at' => now()->subDays(45),
        ]);

        $endpoints[] = WebhookEndpoint::create([
            'merchant_id' => $merchant->id,
            'url' => 'https://staging.mystore.com/hooks/payments',
            'secret' => 'whsec_' . Str::random(32),
            'description' => 'Staging environment',
            'events' => [],
            'status' => 'active',
            'failure_count' => 3,
            'last_success_at' => now()->subDays(2),
            'last_failure_at' => now()->subHours(4),
            'created_at' => now()->subDays(30),
        ]);

        $endpoints[] = WebhookEndpoint::create([
            'merchant_id' => $merchant->id,
            'url' => 'https://old-system.mystore.com/notify',
            'secret' => 'whsec_' . Str::random(32),
            'description' => 'Legacy system (deprecated)',
            'events' => ['payment.completed'],
            'status' => 'disabled',
            'failure_count' => 12,
            'last_failure_at' => now()->subDays(10),
            'disabled_at' => now()->subDays(10),
            'created_at' => now()->subDays(90),
        ]);

        $this->command->info('3 webhook endpoints created.');

        // Create 25 webhook events
        $eventTypes = [
            'payment.created', 'payment.completed', 'payment.failed', 'payment.refunded',
            'customer.created', 'customer.updated', 'payout.initiated', 'payout.completed',
        ];
        $statuses = ['delivered', 'delivered', 'delivered', 'delivered', 'failed', 'pending'];

        for ($i = 0; $i < 25; $i++) {
            $type = $eventTypes[array_rand($eventTypes)];
            $status = $statuses[array_rand($statuses)];
            $createdAt = now()->subHours(rand(1, 720));

            $event = WebhookEvent::create([
                'merchant_id' => $merchant->id,
                'type' => $type,
                'payload' => json_encode([
                    'id' => 'evt_' . Str::random(16),
                    'type' => $type,
                    'created' => $createdAt->timestamp,
                    'data' => [
                        'object' => [
                            'id' => 'txn_' . Str::random(16),
                            'amount' => rand(500, 50000),
                            'currency' => ['usd', 'eur', 'gbp'][array_rand(['usd', 'eur', 'gbp'])],
                            'status' => 'succeeded',
                        ],
                    ],
                ]),
                'status' => $status,
                'is_test' => true,
                'created_at' => $createdAt,
            ]);

            // Create delivery records for active endpoints
            foreach (array_slice($endpoints, 0, 2) as $ep) {
                if ($ep->subscribesTo($type)) {
                    WebhookDelivery::create([
                        'webhook_event_id' => $event->id,
                        'webhook_endpoint_id' => $ep->id,
                        'url' => $ep->url,
                        'http_status' => $status === 'delivered' ? 200 : ($status === 'failed' ? [500, 502, 408][array_rand([500, 502, 408])] : null),
                        'request_headers' => json_encode(['Content-Type' => 'application/json', 'X-PlutoPay-Signature' => 'sha256=' . Str::random(64)]),
                        'request_body' => $event->payload,
                        'response_headers' => $status === 'delivered' ? json_encode(['Content-Type' => 'application/json']) : null,
                        'response_body' => $status === 'delivered' ? json_encode(['received' => true]) : ($status === 'failed' ? 'Internal Server Error' : null),
                        'attempt_number' => $status === 'failed' ? rand(1, 5) : 1,
                        'response_time_ms' => $status !== 'pending' ? rand(50, 2000) : null,
                        'status' => $status,
                        'error_message' => $status === 'failed' ? 'Server returned HTTP ' . [500, 502, 408][array_rand([500, 502, 408])] : null,
                        'delivered_at' => $status === 'delivered' ? $createdAt->addSeconds(rand(1, 5)) : null,
                        'created_at' => $createdAt,
                    ]);
                }
            }
        }

        $this->command->info('25 webhook events + deliveries created!');
    }
}
