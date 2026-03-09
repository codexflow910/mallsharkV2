<?php

namespace Packages\Product\Models\V4;

use Illuminate\Database\Eloquent\Model;

class CustomImageSetting extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'custom_image_settings';
    protected $fillable = [
        'product_id',
        'min_width',
        'min_height',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
