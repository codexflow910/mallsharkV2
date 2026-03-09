<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PostCategory
 *
 * @author Asif
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategory  extends Model {
    //put your code here
     protected $table = 'post_category';
     public $timestamps = false;
}
