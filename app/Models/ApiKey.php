<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'merchant_id', 'name', 'type', 'key', 'key_hash', 'key_last_four',
        'is_test', 'scopes', 'last_used_at', 'expires_at', 'revoked_at', 'created_by',
    ];

    protected $hidden = ['key', 'key_hash'];

    protected $casts = [
        'scopes' => 'array',
        'is_test' => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function merchant() { return $this->belongsTo(Merchant::class); }
    public function createdBy() { return $this->belongsTo(MerchantUser::class, 'created_by'); }

    public function isRevoked(): bool { return $this->revoked_at !== null; }
    public function isExpired(): bool { return $this->expires_at && $this->expires_at->isPast(); }
    public function isValid(): bool { return !$this->isRevoked() && !$this->isExpired(); }

    public function scopeValid($query)
    {
        return $query->whereNull('revoked_at')->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
