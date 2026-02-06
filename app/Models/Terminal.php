<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Terminal extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'merchant_id', 'name', 'serial_number', 'model', 'status',
        'location_name', 'location_address',
        'processor_terminal_id', 'processor_location_id', 'processor_metadata',
        'firmware_version', 'battery_level', 'is_test',
        'last_seen_at', 'paired_at', 'metadata',
    ];

    protected $casts = [
        'processor_metadata' => 'array',
        'metadata' => 'array',
        'is_test' => 'boolean',
        'last_seen_at' => 'datetime',
        'paired_at' => 'datetime',
    ];

    public function merchant() { return $this->belongsTo(Merchant::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }

    public function isOnline(): bool { return $this->status === 'online'; }
    public function scopeOnline($query) { return $query->where('status', 'online'); }
}
