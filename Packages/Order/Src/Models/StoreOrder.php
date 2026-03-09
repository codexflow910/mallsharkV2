<?php

namespace Packages\Order\Models;

use Packages\Product\Models\{Product,Attachments};
use Packages\Order\Models\OrderItems;
use Packages\Order\Models\ProductRefund;
use Packages\Home\Models\ShippingAddress;
use Packages\Home\Models\Store;
use Packages\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;

class StoreOrder extends Model
{

    protected $primaryKey = 'order_id';
    protected $table = 'store_orders';

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'store_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'user_id', 'buyer_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItems::class, 'order_id', 'order_id');
    }

    public function orderItemsList()
    {
        return $this->hasMany(OrderItems::class, 'order_id', 'order_id')->with('products');
    }

    public function refundItems()
    {
        return $this->hasMany(ProductRefund::class, 'order_id', 'order_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'product_id', 'item_id')->with('productImages');
    }

    public function productsWithItems()
    {
        return $this->hasManyThrough(Product::class, OrderItems::class, 'order_id', 'product_id', 'order_id', 'item_id');
    }

    public function productImages()
    {
        return $this->belongsTo(Attachments::class, 'attachment_id', 'feature_image');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_address', 'sh_id')->with('country', 'state');
    }
}
