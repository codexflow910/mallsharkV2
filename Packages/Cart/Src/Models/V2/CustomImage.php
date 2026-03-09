<?php

namespace Packages\Cart\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomImage extends Model
{
    use HasFactory;

    protected $table = 'custom_images';

    protected $fillable = [
        'cart_item_id',
        'file_name',
        'price',
        'instructions',
        'product_quantity',
        'is_check',
    ];

    protected $casts = [
        'cart_item_id' => 'integer',
        'file_name' => 'string',
        'price' => 'decimal:2',
        'instructions' => 'string',
        'product_quantity' => 'integer',
    ];

    public function cartItem()
    {
        return $this->belongsTo(ProductCart::class, 'cart_item_id', 'cart_id');
    }
}
