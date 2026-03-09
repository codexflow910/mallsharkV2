<?php

namespace Packages\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedShipping extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_type',
        'type_id',
        'shipping_per_item',
        'per_item_price',
        'max_items_allowed_for_price',
        'additional_price',
        'shipping_by_weight',
        'base_price',
        'weight_unit',
        'base_price_per_weight',
        'max_weight_allowed_for_price',
        'additional_price_weight',
        'expected_delivery_time',
    ];

    public function zone() {
        return $this->belongsTo(Zone::class, 'type_id');
    }

    public function fixedShipping()
    {
        return $this->hasMany(FixedShipping::class, 'type_id', 'id');
    }

    public function carrierShipping()
    {
        return $this->hasMany(CarrierBasedShipping::class, 'type_id', 'id');
    }
}
