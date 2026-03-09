<?php

namespace Packages\Core\Models\V4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Packages\Store\Models\V4\Store;

class MallsharkPurchasedPackage extends Model
{
    use HasFactory;
   protected $table = 'mallshark_purchased_packages';
   
    protected $fillable = [
        'store_id',
        'package_name',
        'amount',
        'started_at',
        'expired_at',
        'status',
        'charge_status',
        'session_id',
        'created_at',
        'updated_at',
    ];
    
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
