<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class category extends Model
{
    protected $table='categories';
    protected $fillable = ['status'];
    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parentCategoryID', 'id');
    }

    // Define the child categories relationship
    public function childCategories()
    {
        return $this->hasMany(Category::class, 'parentCategoryID', 'id');
    }
    
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_category', 'category_id', 'product_id');
    }

    public function applicables()
    {
        return $this->morphMany(DiscountApplicable::class, 'applicable');
    }
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parentCategoryID');
    }
    public function children()
    {
        return $this->hasMany(Category::class, 'parentCategoryID');
    }
}
