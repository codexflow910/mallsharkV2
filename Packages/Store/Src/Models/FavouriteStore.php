<?php

namespace Packages\Store\Models;

use Illuminate\Database\Eloquent\Model;

class FavouriteStore extends Model {
    protected $fillable = ['user_id', 'store_id'];
}
