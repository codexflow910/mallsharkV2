<?php

namespace Packages\Shipping\Models\V2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingCarrier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'logo', 'is_active'];
}
