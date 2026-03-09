<?php

namespace Packages\Discount\Models;

use App\Models\User;
use App\Models\StoreOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiscountUsage extends Model
{
    use HasFactory;

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
