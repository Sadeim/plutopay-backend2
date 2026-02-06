<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebhookEndpoint extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'merchant_id', 'url', 'secret', 'events', 'status',
        'description', 'failure_count', 'last_success_at', 'last_failure_at', 'disabled_at',
    ];

    protected $hidden = ['secret'];

    protected $casts = [
        'events' => 'array',
        'last_success_at' => 'datetime',
        'last_failure_at' => 'datetime',
        'disabled_at' => 'datetime',
    ];

    public function merchant() { return $this->belongsTo(Merchant::class); }
    public function deliveries() { return $this->hasMany(WebhookDelivery::class); }

    public function subscribesTo(string $event): bool
    {
        if (empty($this->events)) return true;
        return in_array($event, $this->events);
    }
}
