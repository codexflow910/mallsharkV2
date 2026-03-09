<?php

namespace Packages\Shipping\Models\V3;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Packages\Product\Models\V3\Product;
use Packages\Store\Models\V3\Store;
use Packages\Import\Models\V3\ProcessTime;

class ShippingOption extends Model
{
    use HasFactory;
    protected $table = 'shipping_options';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function store()
    {
        return $this->belongsTo(Store::class, 'user_id');
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function processingTime()
    {
        return $this->belongsTo(ProcessTime::class, 'processing_time_id');
    }
}
