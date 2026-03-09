<?php

namespace Packages\Store\Models\V3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreEscrow extends Model
{
    use HasFactory;
    protected $primaryKey = 'order_id';
    protected $table = 'store_escrows';
}
