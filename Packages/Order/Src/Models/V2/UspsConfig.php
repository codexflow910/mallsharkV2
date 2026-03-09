<?php

namespace Packages\Order\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Packages\Store\Models\V2\Store;

class UspsConfig extends Model
{
    protected $table = 'usps_config';

    protected $fillable = [
        'store_id',
        'consumer_key',
        'consumer_secret',
        'crid',
        'mailer_id',
        'eps_number',
    ];

    /**
     * Each store has one USPS config
     */
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'store_id');
    }
}
