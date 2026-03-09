<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Filters
 *
 * @author Ahsan
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filters extends Model {

    protected $primaryKey = 'filter_id';
    protected $table = 'filters';
    public $timestamps = false;

}
