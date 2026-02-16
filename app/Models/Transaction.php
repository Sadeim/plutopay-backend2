<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'merchant_id', 'customer_id', 'reference', 'type', 'status',
        'amount', 'amount_refunded',
        'tip_amount', 'currency', 'payment_method_type',
        'card_brand', 'card_last_four', 'card_exp_month', 'card_exp_year',
        'source', 'terminal_id', 'processor_type', 'processor_transaction_id',
        'processor_response', 'idempotency_key', 'description', 'failure_reason',
        'failure_code', 'receipt_email', 'receipt_url',
        'billing_address', 'shipping_address',
        'is_test', 'metadata', 'captured_at', 'failed_at', 'refunded_at', 'disputed_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'amount_refunded' => 'integer',
        'processor_response' => 'array',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'metadata' => 'array',
        'is_test' => 'boolean',
        'captured_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'disputed_at' => 'datetime',
    ];

    protected $hidden = ['processor_response'];

    public function merchant() { return $this->belongsTo(Merchant::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function terminal() { return $this->belongsTo(Terminal::class); }
    public function refunds() { return $this->hasMany(Refund::class); }
    public function disputes() { return $this->hasMany(Dispute::class); }

    public function scopeSuccessful($query) { return $query->where('status', 'succeeded'); }
    public function scopeFailed($query) { return $query->where('status', 'failed'); }

    public function isRefundable(): bool
    {
        return $this->status === 'succeeded' && $this->amount_refunded < $this->amount;
    }

    public function getRemainingRefundableAttribute(): int
    {
        return $this->amount - $this->amount_refunded;
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount / 100, 2) . ' ' . strtoupper($this->currency);
    }
}
