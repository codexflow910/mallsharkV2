<?php

namespace Packages\Cart\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItemCustomization extends Model
{
    use HasFactory;

    protected $table = 'cart_item_customizations';
    
    protected $fillable = [
        'cart_item_id',
        'composite_key',
        'styles',
        'custom_text',
        'instructions',
        'product_quantity',
        'price',
        'is_check',
    ];

    protected $casts = [
        'cart_item_id' => 'integer',
        'styles' => 'array',
        'custom_text' => 'string',
        'instructions' => 'string',
        'product_quantity' => 'string',
        'price' => 'decimal:2',
        'composite_key' => 'string',
    ];

    public function cartItem()
    {
        return $this->belongsTo(ProductCart::class, 'cart_item_id', 'cart_id');
    }
}
