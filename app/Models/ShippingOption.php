<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    // In ShippingOption model
    public function processingTime()
    {
        return $this->belongsTo(ProcessTime::class, 'processing_time_id');
    }

}
