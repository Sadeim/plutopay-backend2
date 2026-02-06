<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'webhook_event_id', 'webhook_endpoint_id', 'url', 'http_status',
        'request_headers', 'request_body', 'response_headers', 'response_body',
        'attempt_number', 'response_time_ms', 'status', 'error_message',
        'next_retry_at', 'delivered_at',
    ];

    protected $casts = [
        'next_retry_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function event() { return $this->belongsTo(WebhookEvent::class, 'webhook_event_id'); }
    public function endpoint() { return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id'); }
}
