<?php

namespace Packages\Shipping\Models\V2;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreShippingCarrier extends Model
{
    use HasFactory;

    protected $table = 'store_shipping_carriers';

    protected $fillable = [
        'store_id',
        'shipping_carrier_id',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function carrier()
    {
        return $this->belongsTo(ShippingCarrier::class, 'shipping_carrier_id');
    }    
}
