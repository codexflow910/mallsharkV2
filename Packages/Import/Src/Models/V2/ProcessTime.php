<?php

namespace Packages\Import\Models\V2;

use Illuminate\Database\Eloquent\Model;

class ProcessTime extends Model {

    protected $primaryKey = 'processing_time_id';
    protected $table = 'processing_time';
    public $timestamps = false;

}