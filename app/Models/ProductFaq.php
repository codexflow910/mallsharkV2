<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class ProductFaq extends Model {

    protected $primaryKey = 'faq_id';
    protected $table = 'product_faqs';
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
