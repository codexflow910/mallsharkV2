<?php

namespace Packages\Order\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Packages\Product\Models\V2\{Product, ProductVariation};
use Packages\Review\Models\V2\Review;
class OrderItems extends Model {

    protected $primaryKey = 'order_item_id';
    protected $table = 'order_items';

    public function storeOrder()
    {
        return $this->belongsTo(StoreOrder::class, 'order_id', 'order_id')
                    ->where('store_id', $this->store_id);
    }

    public function storeProductOrder()
    {
        return $this->belongsTo(StoreOrder::class, 'order_id', 'order_id');
    }
    public function review()
    {
        return $this->hasMany(Review::class, 'product_id', 'item_id');
    }
     
    public function products(){
        return $this->hasMany(Product::class, 'product_id', 'item_id');
    }

    public function product(){
        return $this->belongsTo(Product::class, 'item_id', 'product_id');
    }

    public function variation(){
        return $this->belongsTo(ProductVariation::class, 'variation_id', 'variation_id');
    }

    public function trackingUrl()
    {
        return $this->belongsTo(OrderTracking::class, 'tracking_id');
    }
}
