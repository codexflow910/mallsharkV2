<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;
    protected $guarded=[''];

    public function applicables()
    {
        // return $this->morphToMany(DiscountApplicable::class, 'applicable');
        return $this->morphMany(DiscountApplicable::class, 'applicable');
    }
    public function usages()
    {
        return $this->hasMany(DiscountUsage::class);
    }
    public function excludedProducts()
    {
        return $this->morphedByMany(Product::class, 'applicable')->wherePivot('excluded', true);
    }
    public function excludedSubcategory()
    {
        return $this->morphedByMany(category::class, 'applicable')->wherePivot('excluded', true);
    }
}
