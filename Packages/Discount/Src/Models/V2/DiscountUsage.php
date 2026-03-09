<?php

namespace Packages\Discount\Models\V2;

use App\Models\User;
use App\Models\StoreOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiscountUsage extends Model
{
    use HasFactory;
    protected $fillable = [
        'discount_id',
        'user_id',
        'order_id',
        'store_id',
        'used_at',
        'usage_count',
    ];
    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(StoreOrder::class);
    }
}
