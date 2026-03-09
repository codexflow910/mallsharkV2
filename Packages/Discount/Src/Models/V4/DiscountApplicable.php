<?php

namespace Packages\Discount\Models\V4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountApplicable extends Model
{
    use HasFactory;
    protected $guarded=[''];
    protected $fillable = ['applicable_id'];
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
