<?php

namespace Packages\Order\Models\V4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRefund extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'product_refund';
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'variation_id', 'order_id', 'buyer_id', 'is_refund',
        'store_id', 'quantity', 'price', 'shipping_amount', 'reason_type',
        'reason', 'reject_order_reason', 'is_complete', 'is_reject',
        'refund_status', 'refund_date',
    ];

}
