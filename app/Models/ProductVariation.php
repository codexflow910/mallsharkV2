<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model {

    protected $table = 'product_variation';
    protected $primaryKey = 'variation_id';
     public $timestamps = false;

     public function products()
    {
        return $this->hasMany(Product::class, 'store_id');
    }

    public function productsCart()
    {
        return $this->hasMany(ProductsCart::class);
    }

    public function shippingOptions()
    {
        return $this->hasMany(ShoppingOption::class, 'user_id');
    }

}
