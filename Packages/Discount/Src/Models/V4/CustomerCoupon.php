<?php

namespace Packages\Discount\Models\V4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCoupon extends Model
{
    use HasFactory;
    protected $table = 'customer_coupons';
    protected $fillable = [
        'user_id',
        'coupon_id',
        'status',
    ];

    public function coupon()
    {
        return $this->belongsTo(Discount::class, 'coupon_id','id');
    }
}
