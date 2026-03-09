<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignStore extends Model
{
    public $timestamps = true; 

    protected $table = 'campaign_store';

    public static function getByStoreId($store_id)
    {
        return self::where('store_id', $store_id)->get();
    }
}
