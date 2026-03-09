<?php

namespace Packages\Core\Models\V4;
use Illuminate\Database\Eloquent\Model;
class BaseRate extends Model
{ 
    protected $table = 'base_rates';

    protected $fillable = ['state_id', 'base_rate'];

}