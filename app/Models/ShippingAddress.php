<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model {

    protected $table = 'shipping_addresses';
    protected $primaryKey = 'sh_id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'full_name',
        'country',
        'state',
        'city',
        'street',
        'post_code',
        // Add other attributes here if needed
    ];
    public function __construct(){
        //
    }

    // public function getCountryName()
    // {
    //     return $this->country->name;
    // }

    public function country()
    {
        return $this->belongsTo(Countries::class, 'country','id');
    }

    public function state()
    {
        return $this->belongsTo(StatesList::class, 'state','state_id');
    }

}
