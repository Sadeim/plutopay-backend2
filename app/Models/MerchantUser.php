<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class MerchantUser extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'merchant_id', 'first_name', 'last_name', 'email', 'password',
        'phone', 'avatar_url', 'role', 'permissions', 'status',
        'email_verified_at', 'last_login_at', 'last_login_ip',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'permissions' => 'array',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function merchant() { return $this->belongsTo(Merchant::class); }
    public function apiKeys() { return $this->hasMany(ApiKey::class, 'created_by'); }

    public function getFullNameAttribute(): string { return "{$this->first_name} {$this->last_name}"; }
    public function isOwner(): bool { return $this->role === 'owner'; }
    public function isAdmin(): bool { return in_array($this->role, ['owner', 'admin']); }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) return true;
        return in_array($permission, $this->permissions ?? []);
    }
}
