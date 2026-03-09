<?php

namespace Packages\Product\Models\V4;

use Illuminate\Database\Eloquent\Model;
use Packages\Product\Models\V4\CustomTextStyleOption;

class CustomTextStyle extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'custom_text_styles';
    protected $fillable = [
        'product_id',
        'style_name',
    ];
     public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

     public function options()
    {
        return $this->hasMany(CustomTextStyleOption::class, 'style_id');
    }

}
