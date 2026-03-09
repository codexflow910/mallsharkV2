<?php

namespace Packages\Product\Models\V3;

use Illuminate\Database\Eloquent\Model;

class CategoryFilterValue extends Model
{
    protected $table = 'category_filter_values';

    protected $fillable = [
        'category_filter_id',
        'value',
        'label',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function filter()
    {
        return $this->belongsTo(CategoryFilter::class, 'category_filter_id');
    }
}
