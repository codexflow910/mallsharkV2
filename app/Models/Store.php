<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{

    protected $primaryKey = 'store_id';
    protected $table = 'stores';

    protected $casts = [
        'mission_complete_date' => 'date',
    ];

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
