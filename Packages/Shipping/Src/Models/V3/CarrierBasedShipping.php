<?php

namespace Packages\Shipping\Models\V3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierBasedShipping extends Model
{
    use HasFactory;

    protected $fillable = [
        'carrier_id',
        'profile_type',
        'type_id',
        'additional_fee',
        'fee_type',
        'fee_value',
    ];

    public function carrier()
    {
        return $this->belongsTo(ShippingCarrier::class, 'carrier_id');
    }
}
