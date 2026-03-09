<?php

namespace Packages\Product\Models\V3;

use App\Models\category;
use Illuminate\Database\Eloquent\Model;

class CategoryFilter extends Model
{
    protected $table = 'category_filters';

    protected $fillable = [
        'category_id',
        'filter_key',
        'filter_name',
        'filter_type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(category::class, 'category_id', 'id');
    }

    public function values()
    {
        return $this->hasMany(CategoryFilterValue::class, 'category_filter_id')->orderBy('sort_order');
    }

    public function activeValues()
    {
        return $this->hasMany(CategoryFilterValue::class, 'category_filter_id')->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
