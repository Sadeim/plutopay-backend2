<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'merchant_id', 'reference', 'amount', 'fee', 'net_amount', 'currency',
        'status', 'destination_type', 'destination_last_four',
        'processor_payout_id', 'processor_response', 'failure_reason',
        'is_test', 'metadata', 'estimated_arrival_at', 'arrived_at', 'failed_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'fee' => 'integer',
        'net_amount' => 'integer',
        'processor_response' => 'array',
        'metadata' => 'array',
        'is_test' => 'boolean',
        'estimated_arrival_at' => 'datetime',
        'arrived_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    protected $hidden = ['processor_response'];

    public function merchant() { return $this->belongsTo(Merchant::class); }
}
