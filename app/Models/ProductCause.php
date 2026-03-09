<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCause extends Model {

    protected $primaryKey = 'cause_id';
    protected $table = 'product_causes';
    public $timestamps = false;

}
