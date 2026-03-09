<?php

namespace Packages\Cart\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Packages\Core\Models\Category;
use Packages\Product\Models\Product;
use Packages\Product\Models\ProductVariation;
use Packages\Shipping\Models\ShippingOption;
use Packages\Store\Models\Store;

class ProductCart extends Model
{
    use HasFactory;
    protected $primaryKey = 'cart_id';
    protected $table = 'products_cart';
    
    protected $fillable = [
        'product_id',
        'store_id',
        'product_quantity',
        'random_key',
        'user_id',
        'variation_id'
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_category', 'product_id', 'category_id');
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id', 'variation_id');
    }

    // Define the store relation, assuming the foreign key is store_id
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function shippingOption()
    {
        return $this->belongsTo(ShippingOption::class, 'shipping_option_id', 'id');
    }
}
