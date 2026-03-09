<?php

namespace Packages\Admin\Models\V4;

use Illuminate\Database\Eloquent\Model;

class StoreDeduction extends Model
{
    protected $table = 'store_deduction';

    protected $fillable = [
        'store_id',
        'mall_shark_amount',
        'shipping_check',
        'ads_check',
        'ad_amount',
    ];
}
