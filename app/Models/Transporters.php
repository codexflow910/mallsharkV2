<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transporters extends Model {

    protected $primaryKey = 'transporter_id';
    protected $table = 'transporters';
    public $timestamps = false;

}
