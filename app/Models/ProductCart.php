<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductCart extends Model
{
    use HasFactory;
    protected $primaryKey = 'cart_id';
    protected $table = 'products_cart';
    public $timestamps = false;
    public $fillable = ['cart_count'];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function categories()
    {
        return $this->belongsToMany(category::class, 'product_category', 'product_id', 'category_id');
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
