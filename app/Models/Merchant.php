<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Merchant extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'business_name', 'display_name', 'business_type', 'business_category',
        'tax_id', 'registration_number', 'email', 'phone', 'website',
        'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country',
        'default_currency', 'timezone', 'locale',
        'status', 'kyc_status', 'kyc_submitted_at', 'kyc_approved_at', 'kyc_rejection_reason',
        'processor_type', 'processor_account_id', 'processor_metadata',
        'logo_url', 'icon_url', 'primary_color',
        'test_mode', 'webhook_secret', 'metadata',
    ];

    protected $casts = [
        'processor_metadata' => 'array',
        'metadata' => 'array',
        'test_mode' => 'boolean',
        'kyc_submitted_at' => 'datetime',
        'kyc_approved_at' => 'datetime',
    ];

    protected $hidden = [
        'webhook_secret', 'processor_metadata',
    ];

    // Relationships
    public function users() { return $this->hasMany(MerchantUser::class); }
    public function apiKeys() { return $this->hasMany(ApiKey::class); }
    public function customers() { return $this->hasMany(Customer::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }
    public function refunds() { return $this->hasMany(Refund::class); }
    public function terminals() { return $this->hasMany(Terminal::class); }
    public function webhookEndpoints() { return $this->hasMany(WebhookEndpoint::class); }
    public function webhookEvents() { return $this->hasMany(WebhookEvent::class); }
    public function payouts() { return $this->hasMany(Payout::class); }
    public function disputes() { return $this->hasMany(Dispute::class); }
    public function auditLogs() { return $this->hasMany(AuditLog::class); }

    // Scopes
    public function scopeActive($query) { return $query->where('status', 'active'); }
    public function scopeTestMode($query) { return $query->where('test_mode', true); }
    public function scopeLiveMode($query) { return $query->where('test_mode', false); }

    // Helpers
    public function isActive(): bool { return $this->status === 'active'; }
    public function isKycApproved(): bool { return $this->kyc_status === 'approved'; }
    public function owner() { return $this->users()->where('role', 'owner')->first(); }
}
