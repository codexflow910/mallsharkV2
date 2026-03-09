<?php

namespace Packages\Order\Models\V2;

use Packages\Product\Models\V2\{Product,Attachments};
use Packages\Order\Models\V2\OrderItems;
use Packages\Order\Models\V2\ProductRefund;
use Packages\Home\Models\V2\ShippingAddress;
use Packages\Home\Models\V2\Store;
use Packages\Auth\Models\V2\User;
use Illuminate\Database\Eloquent\Model;

class StoreOrder extends Model
{

    protected $primaryKey = 'order_id';
    protected $table = 'store_orders';
    protected $fillable = [
        'buyer_id',
        'payment_method',
        'payment_status',
        'charge_status',
        'platform_fee',
        'payable_amount',
        'order_status',
        'total_amount',
        'total_items',
        'shipping_address',
        'shipping_method',
        'transaction_id',
        'session_id',
        'balance_transaction',
        'payerID',
        'shipping_amount',
        'store_id',
    ];
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
