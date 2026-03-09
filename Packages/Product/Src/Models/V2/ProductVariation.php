<?php

namespace Packages\Product\Models\V2;

use App\Models\ProductCart;
use App\Models\ShippingOption;
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
        return $this->hasMany(ProductCart::class);
    }

    public function shippingOptions()
    {
        return $this->hasMany(ShippingOption::class, 'user_id');
    }

    public function tiers()
    {
        return $this->hasMany(ProductTier::class, 'variation_id');
    }
}
