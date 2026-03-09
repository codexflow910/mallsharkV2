<?php

namespace Packages\Product\Models\V3;

use Illuminate\Database\Eloquent\Model;

class CustomTextCharacter extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'custom_text_characters';
    protected $fillable = [
        'product_id',
        'characters_limit',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
