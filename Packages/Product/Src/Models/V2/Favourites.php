<?php

namespace Packages\Product\Models\V2;

use Illuminate\Database\Eloquent\Model;

class Favourites extends Model {

    protected $primaryKey = 'favourite_id';
    protected $table = 'favourites';

    protected $fillable = ['user_id', 'product_id'];

}
