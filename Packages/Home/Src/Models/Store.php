<?php

namespace Packages\Home\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Packages\Order\Models\StoreOrder;
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
        return $this->hasMany(ProductsCart::class);
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
