<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifyStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','shop_id','name','email', 'shop_url','domain', 'access_token', 'currency_code',
        'product_management', 'inventory_management', 'order_synchronization',
        'discount_promotion_management', 'shipping_fulfillment'
    ];
}