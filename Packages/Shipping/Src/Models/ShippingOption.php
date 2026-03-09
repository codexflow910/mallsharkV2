<?php

namespace Packages\Shipping\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Packages\Product\Models\Product;
use Packages\Store\Models\Store;
use Packages\Import\Models\ProcessTime;
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
