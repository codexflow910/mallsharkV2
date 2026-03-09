<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UsersExtraModel extends Model {

    protected $primaryKey = 'user_id';
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];


    public function orders()
    {
        return $this->hasMany(StoreOrder::class, 'buyer_id','user_id');
    }
    public function getOrdersCountAttribute()
    {
        return $this->orders->count();
    }
    public function scopeWithOrderCount($query)
    {
        return $query->withCount(['orders as orders_count' => function ($query) {
            $query->select(DB::raw('count(*)'));
        }]);
    }
}
