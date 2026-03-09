<?php

namespace Packages\Product\Models\V2;
use Illuminate\Database\Eloquent\Model;

class ProductAiTags extends Model
{
    protected $table = 'product_ai_tags';
    protected $fillable = ['product_id', 'tag_title'];     
    public $timestamps = true;
}
