<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'merchant_id', 'transaction_id', 'reference', 'amount', 'currency',
        'status', 'reason', 'evidence', 'processor_dispute_id', 'processor_response',
        'is_test', 'metadata', 'evidence_due_at', 'resolved_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'processor_response' => 'array',
        'metadata' => 'array',
        'is_test' => 'boolean',
        'evidence_due_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected $hidden = ['processor_response'];

    public function merchant() { return $this->belongsTo(Merchant::class); }
    public function transaction() { return $this->belongsTo(Transaction::class); }
}
