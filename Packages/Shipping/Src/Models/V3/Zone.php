<?php

namespace Packages\Shipping\Models\V3;

use Packages\Store\Models\V3\States;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_profile_id',
        'name',
        'type',
        'free_shipping_time',
    ];
    
    public function states()
    {
        return $this->belongsToMany(States::class, 'zone_states', 'zone_id', 'state_id');
    }

    public function fixedShipping()
    {
        return $this->hasOne(FixedShipping::class, 'type_id', 'id')->where('profile_type', 'custom');
    }

    public function carrierShipping()
    {
        return $this->hasOne(CarrierBasedShipping::class, 'type_id', 'id')->where('profile_type', 'custom');
    }

    public function freeShipping()
    {
        return $this->hasOne(FreeShipping::class, 'type_id')->where('profile_type', 'custom');
    }
}
