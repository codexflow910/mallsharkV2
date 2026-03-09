<?php

namespace Packages\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingCarrier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'logo', 'is_active'];
}
