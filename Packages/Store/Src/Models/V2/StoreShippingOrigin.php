<?php

namespace Packages\Store\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Packages\Store\Models\V2\Store;
use Packages\Store\Models\V2\Countries;
use Packages\Store\Models\V2\States;

class StoreShippingOrigin extends Model
{
    protected $table = 'store_shipping_origins';

    protected $fillable = [
        'store_id',
        'street',
        'city',
        'store_country',
        'store_state',
        'zip_code',
        
    ];
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    
}
