<?php

namespace Packages\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeShipping extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_type',
        'type_id',
        'free_shipping_time',
    ];
}
