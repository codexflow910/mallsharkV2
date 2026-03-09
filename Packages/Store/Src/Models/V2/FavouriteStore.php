<?php

namespace Packages\Store\Models\V2;

use Illuminate\Database\Eloquent\Model;

class FavouriteStore extends Model {
    protected $fillable = ['user_id', 'store_id'];
}
