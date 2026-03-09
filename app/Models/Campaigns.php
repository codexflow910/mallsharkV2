<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaigns extends Model
{

    public $timestamps = true;

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'campaign_store');
    } 

}
