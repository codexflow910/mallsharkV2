<?php

namespace Packages\Order\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRefund extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'product_refund';
    public $timestamps = false;
}
