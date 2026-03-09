<?php

namespace Packages\Product\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryProduct extends Model
{
    protected $primaryKey = 'id';
     protected $table = 'product_category';
     public $timestamps = false;
}
