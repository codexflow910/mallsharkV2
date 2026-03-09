<?php

namespace Packages\Home\Models\V4;
use Packages\Product\Models\V4\Product;
use Packages\Auth\Models\V4\User;
use Packages\Order\Models\V4\StoreOrder;
use Packages\Cart\Models\V4\ProductCart;
use Packages\Home\Models\V4\Countries;
use Illuminate\Database\Eloquent\Model;
use App\Models\StatesList;
class Store extends Model
{

    protected $primaryKey = 'store_id';
    protected $table = 'stores';

    public function user()
    {
        return $this->hasOne(User::class, 'user_id', 'user_id');
    }
    public function products() {
        return $this->hasMany(Product::class, 'store_id', 'store_id');
    }

    public function cartItems()
    {
        return $this->hasMany(ProductCart::class);
    }
    public function storeOrders()
    {
        return $this->hasMany(StoreOrder::class, 'store_id', 'store_id');
    }
    public function country()
    {
        return $this->belongsTo(Countries::class, 'store_country','id');
    }

    public function state()
    {
        return $this->belongsTo(StatesList::class, 'store_state','state_id');
    }
}
