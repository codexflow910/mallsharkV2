<?php

namespace Packages\Shipping\Models;

use Packages\Shipping\Models\Zone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'address',
        'city',
        'state_id',
        'zip_code',
        'is_general',
    ];

    public function zones()
    {
        return $this->hasMany(Zone::class, 'shipping_profile_id');
    }

    public function fixedShipping()
    {
        return $this->hasOne(FixedShipping::class, 'type_id')->where('profile_type', 'general');
    }

    public function carrierShipping()
    {
        return $this->hasOne(CarrierBasedShipping::class, 'type_id')->where('profile_type', 'general');
    }

    public function freeShipping()
    {
        return $this->hasOne(FreeShipping::class, 'type_id')->where('profile_type', 'general');
    }
}
