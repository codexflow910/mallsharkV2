<?php

namespace Packages\Product\Models\V2;

use Illuminate\Database\Eloquent\Model;

class ProductCustomization extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'product_customizations';
    protected $fillable = [
        'product_id',
        'allow_text',
        'allow_image',
        'price',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
