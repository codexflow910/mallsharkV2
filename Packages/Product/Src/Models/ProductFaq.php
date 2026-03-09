<?php

namespace Packages\Product\Models;


use Illuminate\Database\Eloquent\Model;
use Packages\Product\Models\Product;

class ProductFaq extends Model {

    protected $primaryKey = 'faq_id';
    protected $table = 'product_faqs';
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
