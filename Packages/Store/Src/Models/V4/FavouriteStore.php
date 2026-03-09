<?php

namespace Packages\Store\Models\V4;

use Illuminate\Database\Eloquent\Model;

class FavouriteStore extends Model {
    protected $fillable = ['user_id', 'store_id'];
}
