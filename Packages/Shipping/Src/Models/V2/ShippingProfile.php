<?php

namespace Packages\Shipping\Models\V2;

use Packages\Shipping\Models\V2\Zone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Packages\Product\Models\V2\Product;

class ShippingProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'address',
        'city',
        'country_id',
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

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_shipping', 'shipping_id', 'product_id');
    }
}
