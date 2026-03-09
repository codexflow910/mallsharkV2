<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model {

    protected $primaryKey = 'cart_id';
    protected $table = 'products_cart';

    public function getpeoductInfo(){
        
         return $this->hasOne('App\Models\Product','product_id','product_id');
    }
    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('product_quantity');
    }
    
}
