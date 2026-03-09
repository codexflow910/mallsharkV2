<?php

namespace Packages\Product\Models\V3;

use Illuminate\Database\Eloquent\Model;

class CustomTextStyleOption extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'custom_text_style_options';
    protected $fillable = [
        'style_id',
        'value',
    ];

    public function style()
    {
        return $this->belongsTo(CustomTextStyle::class, 'style_id');
    }
}
