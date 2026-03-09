<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model {

    protected $primaryKey = 'store_campaign_id';
    protected $table = 'store_campaigns';
    public $timestamps = false;
	

}
