<?php

namespace Packages\Discount\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountApplicable extends Model
{
    use HasFactory;
    protected $guarded=[''];

    public function applicable()
    {
        return $this->morphTo();
    }

    public function discounts()
    {
        return $this->morphedByMany(Discount::class, 'applicable');
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discount_id'); // Corrected Relationship
    }
}
