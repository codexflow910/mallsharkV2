<?php

namespace Packages\Order\Models;
use Packages\Product\Models\{Product};
use Packages\Review\Models\Review;
use Illuminate\Database\Eloquent\Model;
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

}
