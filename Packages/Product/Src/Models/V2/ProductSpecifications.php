<?php

namespace Packages\Product\Models\V2;

use Illuminate\Database\Eloquent\Model;

class ProductSpecifications extends Model {
    protected $table = 'product_specifications';

    protected $fillable = [
        'product_id',
        'title',
        'value',
    ];

    public function product() {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

}
