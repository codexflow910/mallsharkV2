<?php

namespace Packages\Cart\Models\V4;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Packages\Core\Models\V4\category;
use Packages\Product\Models\V4\Product;
use Packages\Product\Models\V4\ProductVariation;
use Packages\Shipping\Models\V4\ShippingOption;
use Packages\Store\Models\V4\Store;

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
        'variation_id',
        'crypto_random',
        'guest',
        'is_custom',
        
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

    public function cartItemCustomizations()
    {
        return $this->hasMany(CartItemCustomization::class, 'cart_item_id', 'cart_id');
    }

    public function customImages()
    {
        return $this->hasMany(CustomImage::class, 'cart_item_id', 'cart_id');
    }
}
