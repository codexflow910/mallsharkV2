<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreEscrow extends Model
{
    use HasFactory;
    protected $primaryKey = 'order_id';
    protected $table = 'store_escrows';
}
