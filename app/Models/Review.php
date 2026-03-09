<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{

    protected $primaryKey = 'review_id';

    public function __construct()
    {

    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

}
