<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'merchant_id', 'external_id', 'email', 'name', 'phone',
        'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country',
        'metadata',
    ];

    protected $casts = ['metadata' => 'array'];

    public function merchant() { return $this->belongsTo(Merchant::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }
}
