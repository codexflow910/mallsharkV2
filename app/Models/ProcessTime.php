<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessTime extends Model {

    protected $primaryKey = 'processing_time_id';
    protected $table = 'processing_time';
    public $timestamps = false;

}
