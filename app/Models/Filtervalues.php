<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Filtervalues
 *
 * @author Asif
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filtervalues extends Model {

    protected $primaryKey = 'f_value_id';
    protected $table = 'filter_values';
    public $timestamps = false;

}
