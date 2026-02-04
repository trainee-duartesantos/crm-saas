<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class UserInvite extends Model
{
    protected $table = 'user_invites';

    protected $fillable = [
        'tenant_id',
        'email',
        'role',
        'token',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    protected $attributes = [
        'role' => 'user',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() ?? false;
    }

    public function isAccepted(): bool
    {
        return ! is_null($this->accepted_at);
    }

    public function scopePending($query)
    {
        return $query->whereNull('accepted_at')
                    ->where('expires_at', '>', now());
    }

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }
}

