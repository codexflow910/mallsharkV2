<?php

namespace Packages\Order\Models\V2;

use Illuminate\Database\Eloquent\Model;

class OrderTracking extends Model
{
    protected $table = 'order_tracking';
    protected $fillable = [
        'url',
        'ship_label_url',
        'ship_amount',
        'ship_service',
        'delivery_date',
        'shipped_date',
        'shipped_manually'
    ];
}
