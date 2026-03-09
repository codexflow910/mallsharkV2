<?php

namespace Packages\Order\Models\V3;

use Illuminate\Database\Eloquent\Model;
use Packages\Order\Models\V3\StoreOrder;

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

    public function order()
    {
        return $this->belongsTo(StoreOrder::class, 'order_id', 'order_id');
    }
}
