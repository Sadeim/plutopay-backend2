<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Services\Webhook\WebhookDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RetryFailedWebhooks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $deliveries = WebhookDelivery::where('status', 'failed')
            ->whereNotNull('next_retry_at')
            ->where('next_retry_at', '<=', now())
            ->where('attempt_number', '<', config('plutopay.webhook_max_retries', 5))
            ->limit(100)
            ->get();

        foreach ($deliveries as $delivery) {
            WebhookDispatcher::retry($delivery);
        }
    }
}
