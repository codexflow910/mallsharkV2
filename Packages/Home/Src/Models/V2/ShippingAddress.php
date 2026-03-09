<?php

namespace Packages\Home\Models\V2;
use Packages\Home\Models\V2\Countries;
use App\Models\StatesList;
use Illuminate\Database\Eloquent\Model;
class ShippingAddress extends Model {

    protected $table = 'shipping_addresses';
    protected $primaryKey = 'sh_id';
    public $timestamps = false;
    protected $fillable = [
        'apartment_no',
        'user_id',
        'full_name',
        'country',
        'state',
        'city',
        'street',
        'post_code',
        'apartment_no',
        'is_valid'
    ];
    public function __construct(){
        //
    }

    public function country()
    {
        return $this->belongsTo(Countries::class, 'country','id');
    }

    public function state()
    {
        return $this->belongsTo(StatesList::class, 'state','state_id');
    }

}
