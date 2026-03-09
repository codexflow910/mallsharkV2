<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureProducts extends Model
{
    use HasFactory; 

    protected $primaryKey = 'feature_id';
    protected $table = 'feature_product';
    public $timestamps = false;
}
