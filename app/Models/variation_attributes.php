<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class VariationAttributes extends Model {

    protected $table = 'variation_attributes';
    protected $primaryKey = 'attribute_id';
    public $timestamps = false;

}
