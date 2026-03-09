<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FeatureLisitngPrices
 *
 * @author Asif
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturePrices extends Model {

    protected $primaryKey = 'feature_prices_id';
    protected $table = 'featureprices';
    public $timestamps = false;

}
