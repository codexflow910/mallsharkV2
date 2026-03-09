<?php

namespace Packages\Notifications\Models\V3;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\{States};


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $primaryKey = 'user_id';
    protected $fillable = [
       'name', 'email', 'password','contact_no', 'physical_address', 'about'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //'password', 'remember_token',
    ];
    public function shipping_address(){
        return $this->hasOne(ShippingAddress::class,'user_id','user_id')->with('country');
    }
    public function buyerOrders(){
        return $this->hasMany(StoreOrder::class,'buyer_id','user_id');
    }
    public function get_states(){
        return $this->belongsTo(States::class,'user_state_id','state_id');
    }
    public function device_token(){
        return $this->hasMany(DeviceToken::class,'user_id','user_id');
    }
    public function store()
    {
        return $this->belongsTo(Store::class, 'user_id', 'user_id');
    }
}
