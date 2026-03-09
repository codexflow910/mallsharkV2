<?php

namespace Packages\Import\Models\V4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingShopifyOAuth extends Model
{
    use HasFactory;

    protected $table = 'pending_shopify_oauth';

    protected $fillable = [
        'user_id',
        'shop_url',
        'client_id',
        'client_secret',
        'state',
        'permissions',
        'expires_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'client_secret',
    ];

    /**
     * Check if this pending OAuth has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Scope to find non-expired pending OAuth by state
     */
    public function scopeValidState($query, string $state)
    {
        return $query->where('state', $state)
                     ->where('expires_at', '>', now());
    }
}
