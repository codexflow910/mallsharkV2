<?php

namespace Packages\Product\Models\V4;

use Illuminate\Database\Eloquent\Model;

class ProductProperty extends Model
{
    protected $table = 'product_properties';

    protected $fillable = [
        'product_id',
        'filter_key',
        'value',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
