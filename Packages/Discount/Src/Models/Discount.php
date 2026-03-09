<?php

namespace Packages\Discount\Models;

use Packages\Core\Models\category;
use Packages\Discount\Models\DiscountApplicable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function discountApplicables()
    {
        return $this->hasMany(DiscountApplicable::class, 'discount_id'); // Corrected Relationship
    }
}
