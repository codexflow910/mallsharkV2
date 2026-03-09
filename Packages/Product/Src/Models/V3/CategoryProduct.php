<?php

namespace Packages\Product\Models\V3;

use Illuminate\Database\Eloquent\Model;

class CategoryProduct extends Model
{
    protected $primaryKey = 'id';
     protected $table = 'product_category';
     public $timestamps = false;
}
