<?php

namespace Packages\Product\Models\V4;
use Illuminate\Database\Eloquent\Model;

class ProductTags extends Model
{
     protected $table = 'product_tags';
     protected $fillable = ['product_id', 'tag_title'];     
     public $timestamps = false;
}
