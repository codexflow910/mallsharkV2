<?php

namespace Packages\Product\Models\V4;

use Illuminate\Database\Eloquent\Model;

class Label extends Model {

    protected $fillable = ['title', 'slug'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'label_products', 'label_id', 'product_id');
    }
}