<?php

namespace Packages\Home\Models\V3;
use Packages\Home\Models\V3\Countries;
use App\Models\StatesList;
use Illuminate\Database\Eloquent\Model;
class Cookies extends Model {

    protected $table = 'cookies';
    // protected $primaryKey = 'cookies_id';
        // public $timestamps = false;


    protected $fillable = [
        'type',
        'value',
        'frequency',
        'user_id',
        'updated'
    ];
   
}
