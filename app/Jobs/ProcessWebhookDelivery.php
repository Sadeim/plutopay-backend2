<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Services\Webhook\WebhookDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWebhookDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [60, 300, 1800, 7200, 86400];

    public function __construct(
        public WebhookDelivery $delivery
    ) {}

    public function handle(): void
    {
        WebhookDispatcher::retry($this->delivery);
    }
}
