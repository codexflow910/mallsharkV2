<?php

namespace Packages\Product\Models\V4;


use Illuminate\Database\Eloquent\Model;
use Packages\Product\Models\V4\Product;

class ProductFaq extends Model {

    protected $primaryKey = 'faq_id';
    protected $table = 'product_faqs';
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
