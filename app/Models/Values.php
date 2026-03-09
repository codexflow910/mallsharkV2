<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Values extends Model {

    protected $primaryKey = 'value_id';
    protected $table = 'values';
    public $timestamps = false;

}
