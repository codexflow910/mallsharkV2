<?php

namespace Packages\Shipping\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreShippingCarrier extends Model
{
    use HasFactory;

    protected $table = 'store_shipping_carriers';

    protected $fillable = [
        'store_id',
        'shipping_carrier_id',
        'config',
        'enabled',
    ];

    protected $casts = [
        'config' => 'encrypted:array', // encrypt config
        'enabled' => 'boolean',
    ];
    
}
