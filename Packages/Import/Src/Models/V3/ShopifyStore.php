<?php

namespace Packages\Import\Models\V3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifyStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','shop_id','name','email', 'shop_url','domain', 'access_token', 'currency_code',
        'product_management', 'inventory_management', 'order_synchronization',
        'discount_promotion_management', 'shipping_fulfillment',
        'client_id', 'client_secret', 'auth_type','status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    

    /**
     * Check if this store uses OAuth authentication
     */
    public function usesOAuth(): bool
    {
        return $this->auth_type === 'oauth';
    }
}