<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'merchant_id', 'user_id', 'user_type', 'action',
        'entity_type', 'entity_id', 'old_values', 'new_values',
        'ip_address', 'user_agent', 'source', 'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    public function merchant() { return $this->belongsTo(Merchant::class); }
}
