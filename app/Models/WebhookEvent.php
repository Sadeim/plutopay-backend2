<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'merchant_id', 'type', 'payload', 'status', 'is_test',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_test' => 'boolean',
    ];

    public function merchant() { return $this->belongsTo(Merchant::class); }
    public function deliveries() { return $this->hasMany(WebhookDelivery::class); }
}
