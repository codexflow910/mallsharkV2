<?php

namespace Packages\Search\Models\V4;
use App\Models\StatesList;
use Illuminate\Database\Eloquent\Model;
class RecommendedProduct extends Model {

    protected $table = 'recommended_products';
    // protected $primaryKey = 'cookies_id';
        // public $timestamps = false;


    protected $fillable = [
       
        'products',
        'user_id',
    ];
   
}
