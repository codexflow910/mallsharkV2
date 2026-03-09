<?php

namespace Packages\Store\Models\V2;

use Illuminate\Database\Eloquent\Model;

class Countries extends Model {
    protected $primaryKey = 'country_id';
    protected $table = 'countries';
}
